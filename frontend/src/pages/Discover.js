import { useNavigate } from "react-router-dom";
import { useApp } from "../context/AppContext";
import PhotoBlock from "../components/PhotoBlock";
import PersonalitySliders from "../components/PersonalitySliders";
import PolicyBox from "../components/PolicyBox";
import ReasonForm from "../components/ReasonForm";
import { useState } from "react";

export default function Discover() {
  const {
    currentProfile,
    matchedId,
    skipsRemaining,
    undosRemaining,
    likeCurrent,
    dislikeCurrent,
    skipCurrent,
    profiles,
    deckIndex,
  } = useApp();
  const navigate = useNavigate();
  const [passing, setPassing] = useState(false);

  if (matchedId) {
    navigate("/match", { replace: true });
    return null;
  }

  if (!currentProfile) {
    return (
      <div className="discover-empty">
        <h1>That&rsquo;s everyone.</h1>
        <p>
          You&rsquo;ve met every profile What&rsquo;s Left has right now.
          Check{" "}
          <button className="link-button" onClick={() => navigate("/public-reasons")}>
            public reasons
          </button>{" "}
          to see the record.
        </p>
      </div>
    );
  }

  if (passing) {
    return (
      <ReasonForm
        profile={currentProfile}
        heading="Before you can move on"
        intro="You've chosen to pass. On What's Left, this decision and the reason behind it becomes part of the public record, permanently."
        warningTitle="This will be public and permanent"
        warningBody="Once published, this reason cannot be edited or removed."
        submitLabel="Save and pass"
        onBack={() => setPassing(false)}
        onSubmit={(shortReason, longReason) => {
          dislikeCurrent(shortReason, longReason);
          setPassing(false);
        }}
      />
    );
  }

  return (
    <div className="discover-page">
      <p className="deck-position">
        Profile {deckIndex + 1} of {profiles.length}
      </p>

      <div className="discover-grid">
        <div className="discover-photo-col">
          <PhotoBlock color={currentProfile.color} style={{ height: 420 }} />
          <div className="discover-like-row">
            <button className="btn btn-like" onClick={likeCurrent}>
              Like
            </button>
            <button className="btn btn-dislike" onClick={() => setPassing(true)}>
              Dislike
            </button>
          </div>
          <button
            className="btn btn-skip"
            disabled={skipsRemaining <= 0}
            onClick={skipCurrent}
            title={
              skipsRemaining <= 0
                ? "You already used your one lifetime skip"
                : "Skip without deciding — you only get one, ever"
            }
          >
            {skipsRemaining > 0 ? "Skip (1 lifetime skip left)" : "No skips left"}
          </button>
        </div>

        <div className="discover-info-col">
          <h1>{currentProfile.name}</h1>
          <p className="split-sub">
            {currentProfile.location}: {currentProfile.distance}
          </p>
          <p className="reason-bio">{currentProfile.bio}</p>

          <div className="hobbies-row">
            {currentProfile.hobbies.map((h) => (
              <span className="hobby-pill" key={h}>
                {h}
              </span>
            ))}
          </div>

          <PersonalitySliders personality={currentProfile.personality} />

          <label className="sublabel">Begin to chat:</label>
          <textarea
            rows={4}
            placeholder="You can message once you match."
            disabled
          />
        </div>

        <div className="discover-policy-col">
          <PolicyBox />
          <p className="undo-note">
            {undosRemaining} of 1 undos remaining &middot; {skipsRemaining} of
            1 skips remaining
          </p>
        </div>
      </div>
    </div>
  );
}
