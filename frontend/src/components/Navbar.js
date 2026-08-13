import { Link, useNavigate } from "react-router-dom";
import Logo from "./Logo";
import { useApp } from "../context/AppContext";

export default function Navbar() {
  const { authed, undosRemaining, matchedId, logout } = useApp();
  const navigate = useNavigate();

  return (
    <header className="navbar">
      <Link to="/" className="navbar-brand">
        <Logo size={26} />
        <span>What&rsquo;s Left</span>
      </Link>

      {authed && (
        <span className="navbar-undo">
          {undosRemaining} UNDO{undosRemaining === 1 ? "" : "S"} REMAINING
        </span>
      )}

      <nav className="navbar-links">
        {authed ? (
          <>
            <Link to="/">Home</Link>
            <Link to="/discover">
              {matchedId ? "Match" : "Discover/Match"}
            </Link>
            <Link to="/public-reasons">Public reasons</Link>
            <Link to="/profile" className="navbar-avatar" aria-label="Your profile">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="8" r="4" stroke="currentColor" strokeWidth="1.6" />
                <path
                  d="M4 20c1.5-4 5-6 8-6s6.5 2 8 6"
                  stroke="currentColor"
                  strokeWidth="1.6"
                  strokeLinecap="round"
                />
              </svg>
            </Link>
            <button
              className="navbar-logout"
              onClick={() => {
                logout();
                navigate("/login");
              }}
            >
              Log out
            </button>
          </>
        ) : (
          <Link to="/login">Login</Link>
        )}
      </nav>
    </header>
  );
}
