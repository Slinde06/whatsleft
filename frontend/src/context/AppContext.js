import { createContext, useContext, useEffect, useState } from "react";
import profiles from "../data/profiles";

const AppContext = createContext(null);

const STORAGE_KEY = "whats-left-state-v1";

const defaultState = {
  authed: false,
  user: null, // { firstName, lastName, email, dob, lookingFor, bio, hobbies, photos }
  deckIndex: 0, // pointer into profiles — can only move forward
  skipsRemaining: 1,
  undosRemaining: 1,
  matchedId: null, // profile id currently matched with, or null if free to browse
  // log entries: { id, profileId, type: 'like' | 'dislike' | 'skip' | 'unmatch', shortReason, longReason, timestamp }
  log: [],
};

function loadState() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (raw) return { ...defaultState, ...JSON.parse(raw) };
  } catch (e) {
    // ignore corrupt storage
  }
  return defaultState;
}

export function AppProvider({ children }) {
  const [state, setState] = useState(loadState);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
  }, [state]);

  const currentProfile =
    state.deckIndex < profiles.length ? profiles[state.deckIndex] : null;

  const matchedProfile = state.matchedId
    ? profiles.find((p) => p.id === state.matchedId) || null
    : null;

  function signup(user) {
    setState((s) => ({ ...s, authed: true, user }));
  }

  function login(email) {
    setState((s) => ({
      ...s,
      authed: true,
      user: s.user || {
        firstName: "Sam",
        lastName: "Williams",
        email,
        dob: "12/06/1995",
        lookingFor: "Female",
        bio: "",
        hobbies: [],
        photos: [],
      },
    }));
  }

  function logout() {
    setState((s) => ({ ...s, authed: false }));
  }

  function updateProfile(patch) {
    setState((s) => ({ ...s, user: { ...s.user, ...patch } }));
  }

  // Like the current profile. In this demo, likes always match (there's no
  // second real user) — matching moves the account into the shared space.
  function likeCurrent() {
    if (!currentProfile) return;
    setState((s) => ({
      ...s,
      log: [
        ...s.log,
        {
          id: `${Date.now()}`,
          profileId: currentProfile.id,
          type: "like",
          timestamp: Date.now(),
        },
      ],
      matchedId: currentProfile.id,
      deckIndex: s.deckIndex + 1,
    }));
  }

  // Dislike (pass) requires a public, detailed reason. Called after the
  // reason form is submitted.
  function dislikeCurrent(shortReason, longReason) {
    if (!currentProfile) return;
    setState((s) => ({
      ...s,
      log: [
        ...s.log,
        {
          id: `${Date.now()}`,
          profileId: currentProfile.id,
          type: "dislike",
          shortReason,
          longReason,
          timestamp: Date.now(),
        },
      ],
      deckIndex: s.deckIndex + 1,
    }));
  }

  // Skip — usable exactly once, ever. No reason required.
  function skipCurrent() {
    if (!currentProfile || state.skipsRemaining <= 0) return;
    setState((s) => ({
      ...s,
      skipsRemaining: s.skipsRemaining - 1,
      deckIndex: s.deckIndex + 1,
      log: [
        ...s.log,
        {
          id: `${Date.now()}`,
          profileId: currentProfile.id,
          type: "skip",
          timestamp: Date.now(),
        },
      ],
    }));
  }

  // Unmatch — this spends the account's one lifetime undo. Requires a
  // public, detailed reason. Returns the user to browsing afterward.
  function unmatch(shortReason, longReason) {
    if (!state.matchedId || state.undosRemaining <= 0) return;
    setState((s) => ({
      ...s,
      log: [
        ...s.log,
        {
          id: `${Date.now()}`,
          profileId: s.matchedId,
          type: "unmatch",
          shortReason,
          longReason,
          timestamp: Date.now(),
        },
      ],
      undosRemaining: s.undosRemaining - 1,
      matchedId: null,
    }));
  }

  function resetAccount() {
    localStorage.removeItem(STORAGE_KEY);
    setState(defaultState);
  }

  const value = {
    ...state,
    profiles,
    currentProfile,
    matchedProfile,
    signup,
    login,
    logout,
    updateProfile,
    likeCurrent,
    dislikeCurrent,
    skipCurrent,
    unmatch,
    resetAccount,
  };

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export function useApp() {
  const ctx = useContext(AppContext);
  if (!ctx) throw new Error("useApp must be used within AppProvider");
  return ctx;
}
