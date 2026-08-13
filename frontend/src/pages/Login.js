import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useApp } from "../context/AppContext";

export default function Login() {
  const { login } = useApp();
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  function handleSubmit(e) {
    e.preventDefault();
    login(email || "exsample@gmail.com");
    navigate("/discover");
  }

  return (
    <div className="split-screen">
      <div className="split-left">
        <blockquote>
          &lsquo;You get one undo, ever. Log in and remember what that
          means.&rdquo;
          <cite>&mdash;What&rsquo;s Left</cite>
        </blockquote>
      </div>

      <div className="split-right">
        <p className="eyebrow">Welcome back</p>
        <h1>Log in</h1>
        <p className="split-sub">Your record is exactly as you left it.</p>

        <form onSubmit={handleSubmit}>
          <label htmlFor="email">Email:</label>
          <input
            id="email"
            type="email"
            placeholder="exsample@gmail.com"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />

          <label htmlFor="password">Password:</label>
          <input
            id="password"
            type="password"
            placeholder="**************************"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />

          <button type="submit" className="btn btn-primary btn-block">
            Log in
          </button>
        </form>

        <div className="or-divider">OR</div>
        <p className="split-footer">
          Don&rsquo;t have an account? <Link to="/signup">Sign up</Link>
        </p>
      </div>
    </div>
  );
}
