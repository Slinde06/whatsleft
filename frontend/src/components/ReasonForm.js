import { useState } from "react";
import PhotoBlock from "./PhotoBlock";

export default function ReasonForm({
  profile,
  heading,
  intro,
  warningTitle,
  warningBody,
  submitLabel,
  onBack,
  onSubmit,
}) {
  const [shortReason, setShortReason] = useState("");
  const [longReason, setLongReason] = useState("");
  const [error, setError] = useState("");

  function handleSubmit(e) {
    e.preventDefault();
    if (!shortReason.trim() || !longReason.trim()) {
      setError("A short and long description are both required — this goes on the public record.");
      return;
    }
    onSubmit(shortReason.trim(), longReason.trim());
  }

  return (
    <div className="reason-page">
      <div className="reason-left">
        <h1>{heading}</h1>
        <p className="split-sub">{intro}</p>

        <div className="reason-photo-frame">
          <PhotoBlock color={profile.color} />
        </div>

        <h2 className="reason-name">{profile.name}</h2>
        <p className="reason-location">
          {profile.location}: {profile.distance}
        </p>
        <p className="reason-bio">{profile.bio}</p>
      </div>

      <div className="reason-right">
        <h2 className="accent-heading">Tell the record why.</h2>

        <form onSubmit={handleSubmit}>
          <label>Reason:</label>
          <label className="sublabel">short description:</label>
          <input
            value={shortReason}
            onChange={(e) => setShortReason(e.target.value)}
            placeholder="e.g. Not enough in common"
          />

          <label className="sublabel">
            Long description (as much detail as possible):
          </label>
          <textarea
            rows={6}
            value={longReason}
            onChange={(e) => setLongReason(e.target.value)}
            placeholder="Explain your decision in detail — this will be visible to others."
          />

          <div className="policy-box policy-box-warning">
            <p className="policy-title">{warningTitle}</p>
            <ul>
              <li>{warningBody}</li>
            </ul>
          </div>

          {error && <p className="form-error">{error}</p>}

          <div className="reason-actions">
            <button type="button" className="btn btn-secondary" onClick={onBack}>
              Back
            </button>
            <button type="submit" className="btn btn-primary">
              {submitLabel}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
