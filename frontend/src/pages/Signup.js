import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useApp } from "../context/AppContext";
import PolicyBox from "../components/PolicyBox";

export default function Signup() {
  const { signup } = useApp();
  const navigate = useNavigate();
  const [form, setForm] = useState({
    firstName: "",
    lastName: "",
    email: "",
    dob: "",
    lookingFor: "",
    password: "",
    confirmPassword: "",
  });
  const [agreed, setAgreed] = useState(false);
  const [error, setError] = useState("");

  function update(field, value) {
    setForm((f) => ({ ...f, [field]: value }));
  }

  function handleSubmit(e) {
    e.preventDefault();
    if (!form.firstName || !form.email || !form.password) {
      setError("Please fill in your name, email, and password.");
      return;
    }
    if (form.password !== form.confirmPassword) {
      setError("Passwords don't match.");
      return;
    }
    if (!agreed) {
      setError("You need to accept the Verdict Policy to join.");
      return;
    }
    signup({
      firstName: form.firstName,
      lastName: form.lastName,
      email: form.email,
      dob: form.dob,
      lookingFor: form.lookingFor,
      bio: "",
      hobbies: [],
      photos: [],
    });
    navigate("/discover");
  }

  return (
    <div className="signup-page">
      <p className="eyebrow center">Join What&rsquo;s Left</p>
      <h1 className="center">Create your account</h1>
      <p className="split-sub center">Read carefully.</p>

      <form className="signup-form" onSubmit={handleSubmit}>
        <div className="form-row">
          <div>
            <label>First Name:</label>
            <input
              placeholder="Sam"
              value={form.firstName}
              onChange={(e) => update("firstName", e.target.value)}
            />
          </div>
          <div>
            <label>Last Name:</label>
            <input
              placeholder="Williams"
              value={form.lastName}
              onChange={(e) => update("lastName", e.target.value)}
            />
          </div>
        </div>

        <label>Email:</label>
        <input
          type="email"
          placeholder="exsample@gmail.com"
          value={form.email}
          onChange={(e) => update("email", e.target.value)}
        />

        <div className="form-row">
          <div>
            <label>Date of birth</label>
            <input
              placeholder="12/06/1995"
              value={form.dob}
              onChange={(e) => update("dob", e.target.value)}
            />
          </div>
          <div>
            <label>Looking for</label>
            <input
              placeholder="Female"
              value={form.lookingFor}
              onChange={(e) => update("lookingFor", e.target.value)}
            />
          </div>
        </div>

        <div className="form-row">
          <div>
            <label>Password:</label>
            <input
              type="password"
              placeholder="**************************"
              value={form.password}
              onChange={(e) => update("password", e.target.value)}
            />
          </div>
          <div>
            <label>Confirm Password:</label>
            <input
              type="password"
              placeholder="**************************"
              value={form.confirmPassword}
              onChange={(e) => update("confirmPassword", e.target.value)}
            />
          </div>
        </div>

        <PolicyBox />

        <label className="agree-row">
          <input
            type="checkbox"
            checked={agreed}
            onChange={(e) => setAgreed(e.target.checked)}
          />
          I&rsquo;ve read the Verdict Policy and I accept it.
        </label>

        {error && <p className="form-error">{error}</p>}

        <button type="submit" className="btn btn-primary btn-block">
          Create Account
        </button>

        <div className="or-divider">OR</div>
        <p className="split-footer center">
          Already have an account? <Link to="/login">Log in</Link>
        </p>
      </form>
    </div>
  );
}
