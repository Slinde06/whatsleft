import { useState } from "react";
import { useApp } from "../context/AppContext";
import PhotoBlock from "../components/PhotoBlock";

export default function EditProfile() {
  const { user, updateProfile, skipsRemaining, undosRemaining, log } = useApp();
  const [form, setForm] = useState({
    firstName: user?.firstName || "",
    lastName: user?.lastName || "",
    email: user?.email || "",
    lookingFor: user?.lookingFor || "",
    bio: user?.bio || "",
    hobbiesText: (user?.hobbies || []).join(", "),
  });
  const [saved, setSaved] = useState(false);

  const publicReasonsCount = log.filter(
    (e) => e.type === "dislike" || e.type === "unmatch"
  ).length;

  function update(field, value) {
    setForm((f) => ({ ...f, [field]: value }));
    setSaved(false);
  }

  function handleSave(e) {
    e.preventDefault();
    updateProfile({
      firstName: form.firstName,
      lastName: form.lastName,
      email: form.email,
      lookingFor: form.lookingFor,
      bio: form.bio,
      hobbies: form.hobbiesText
        .split(",")
        .map((h) => h.trim())
        .filter(Boolean),
    });
    setSaved(true);
  }

  return (
    <div className="edit-profile-page">
      <div className="edit-photo-col">
        <PhotoBlock color="#325D80" style={{ height: 600 }} />
      </div>

      <div className="edit-form-col">
        <h1>Customize your profile:</h1>
        <p className="split-sub">
          This is your only pitch — make it count. Changes here save
          privately until you publish.
        </p>

        <p className="section-label">Photos:</p>
        <p className="split-sub">
          Add up to 6 photos. Your first photo is shown first in Discover.
        </p>
        <div className="photo-grid">
          <PhotoBlock color="#6D5B7F" />
          <PhotoBlock color="#6D5B7F" />
          <PhotoBlock color="#6D5B7F" />
          <button type="button" className="photo-add-btn">
            + ADD
          </button>
        </div>

        <hr />

        <form onSubmit={handleSave}>
          <p className="section-label">Info:</p>
          <div className="form-row">
            <div>
              <label>First Name:</label>
              <input
                value={form.firstName}
                onChange={(e) => update("firstName", e.target.value)}
              />
            </div>
            <div>
              <label>Last Name:</label>
              <input
                value={form.lastName}
                onChange={(e) => update("lastName", e.target.value)}
              />
            </div>
          </div>

          <label>Email:</label>
          <input
            value={form.email}
            onChange={(e) => update("email", e.target.value)}
          />

          <div className="form-row">
            <div>
              <label>Date of birth (can&rsquo;t edit)</label>
              <input value={user?.dob || ""} disabled />
            </div>
            <div>
              <label>Looking for</label>
              <input
                value={form.lookingFor}
                onChange={(e) => update("lookingFor", e.target.value)}
              />
            </div>
          </div>

          <label>Description of you:</label>
          <textarea
            rows={4}
            value={form.bio}
            onChange={(e) => update("bio", e.target.value)}
            placeholder="Trail runs on Saturdays, terrible at board games..."
          />

          <label>Hobbies/Likes (comma separated):</label>
          <input
            value={form.hobbiesText}
            onChange={(e) => update("hobbiesText", e.target.value)}
            placeholder="biking, Food, Bikes, Cars"
          />

          <div className="policy-box">
            <p className="policy-title">PERMANENT ACCOUNT RULES</p>
            <ul>
              <li>Skips available: {skipsRemaining} of 1 remaining</li>
              <li>Undos available: {undosRemaining} of 1 remaining</li>
              <li>Public reasons on record: {publicReasonsCount} published</li>
              <li>Profile deletion: Not permitted</li>
            </ul>
          </div>

          {saved && <p className="form-success">Saved.</p>}

          <button type="submit" className="btn btn-primary">
            Save changes
          </button>
        </form>
      </div>
    </div>
  );
}
