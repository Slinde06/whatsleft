import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useApp } from "../context/AppContext";
import PhotoBlock from "../components/PhotoBlock";
import ReasonForm from "../components/ReasonForm";

export default function MatchSpace() {
  const { matchedProfile, matchedId, undosRemaining, unmatch, log } = useApp();
  const navigate = useNavigate();
  const [unmatching, setUnmatching] = useState(false);

  if (!matchedId || !matchedProfile) {
    return (
      <div className="discover-empty">
        <h1>No active match</h1>
        <p>You&rsquo;re free to browse — head back to Discover.</p>
        <button className="btn btn-primary" onClick={() => navigate("/discover")}>
          Go to Discover
        </button>
      </div>
    );
  }

  if (unmatching) {
    return (
      <ReasonForm
        profile={matchedProfile}
        heading="Before you can match again"
        intro="You've chosen to unmatch. On What's Left, this decision and the reason behind it becomes part of your public profile and theirs, permanently."
        warningTitle="This will be public and permanent"
        warningBody="Once published, this reason cannot be edited or removed. You have one undo across your entire account — using it here means you can't use it again later."
        submitLabel="Save and unmatch"
        onBack={() => setUnmatching(false)}
        onSubmit={(shortReason, longReason) => {
          unmatch(shortReason, longReason);
          navigate("/discover");
        }}
      />
    );
  }

  const history = log.filter(
    (entry) => entry.profileId === matchedProfile.id && entry.type === "unmatch"
  );

  return (
    <div className="match-page">
      <div className="match-side">
        <PhotoBlock color={matchedProfile.color} />
        <h1>{matchedProfile.name}</h1>
        <p className="split-sub">
          {matchedProfile.location}: {matchedProfile.distance}
        </p>
        <div className="hobbies-row">
          {matchedProfile.hobbies.map((h) => (
            <span className="hobby-pill" key={h}>
              {h}
            </span>
          ))}
        </div>

        <p className="section-label">Previous match record</p>
        {history.length === 0 ? (
          <p className="split-sub">No previous history with {matchedProfile.name}.</p>
        ) : (
          history.map((entry) => (
            <div className="history-row" key={entry.id}>
              <PhotoBlock color={matchedProfile.color} className="history-thumb" />
              <div>
                <strong>{matchedProfile.name}</strong>
                <p>{entry.longReason}</p>
              </div>
            </div>
          ))
        )}
      </div>

      <div className="match-chat">
        <div className="match-chat-header">
          <PhotoBlock color={matchedProfile.color} className="chat-avatar" />
          <h2>{matchedProfile.name}</h2>
        </div>
        <div className="match-chat-body">
          <p className="chat-placeholder">
            You&rsquo;re matched. This is your shared space — you can&rsquo;t
            browse anyone else until it ends.
          </p>
        </div>
        <div className="match-actions">
          <button className="btn btn-secondary" onClick={() => navigate("/discover")}>
            Back
          </button>
          <button
            className="btn btn-unmatch"
            disabled={undosRemaining <= 0}
            onClick={() => setUnmatching(true)}
            title={
              undosRemaining <= 0
                ? "You've already used your one lifetime undo"
                : "Unmatch — this spends your one lifetime undo"
            }
          >
            {undosRemaining > 0 ? "Unmatch" : "No undos left"}
          </button>
        </div>
      </div>
    </div>
  );
}
