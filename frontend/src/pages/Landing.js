import { Link } from "react-router-dom";
import { useApp } from "../context/AppContext";

export default function Landing() {
  const { authed, matchedId } = useApp();
  const discoverTarget = authed ? "/discover" : "/signup";

  return (
    <div className="landing">
      <section className="landing-hero">
        <div className="landing-swatch landing-swatch-left" aria-hidden="true" />

        <div className="landing-copy">
          <p className="eyebrow">A dating site with a memory</p>
          <h1>
            The right match
            <br />
            <span className="accent">changes everything,</span>
          </h1>
          <p className="landing-sub">
            Every choice you make here is permanent and public. No ghosting,
            no deleting, no do-overs — just one honest shot at the right
            match.
          </p>

          <div className="landing-features">
            <div>
              <h3>One match changes your whole site</h3>
              <p>
                The moment you match, your entire experience moves to a new,
                private space built for the two of you. There&rsquo;s no
                going back to browsing.
              </p>
            </div>
            <div>
              <h3>One undo. Ever.</h3>
              <p>
                Across your entire time on What&rsquo;s Left, you get exactly
                one undo. Spend it carefully — it doesn&rsquo;t come back.
              </p>
            </div>
            <div>
              <h3>Nothing is ever deleted</h3>
              <p>
                Every match, unmatch, and reason you give stays on the record
                — visible to others — for as long as you&rsquo;re a member.
              </p>
            </div>
          </div>

          <p className="eyebrow" style={{ marginTop: "2.5rem" }}>
            How it works
          </p>
          <h2 className="landing-steps-title">Four steps. No undo.</h2>

          <div className="landing-steps">
            <div>
              <h4>Build your profile</h4>
              <p>Sign up and tell the truth. Your profile is the only pitch you get.</p>
            </div>
            <div>
              <h4>Meet one person at a time</h4>
              <p>You&rsquo;re shown one profile at a time. You can go to the next one, but never back.</p>
            </div>
            <div>
              <h4>Decide</h4>
              <p>Pass and explain why in public, or match and mean it.</p>
            </div>
            <div>
              <h4>Live in the new space</h4>
              <p>Matching transforms your account into a shared space.</p>
            </div>
          </div>
        </div>

        <div className="landing-swatch landing-swatch-right" aria-hidden="true" />
      </section>

      <section className="landing-cta">
        <h2>Ready to make a decision that matters?</h2>
        <p>Go discover what&rsquo;s left.</p>
        <Link to={matchedId ? "/discover" : discoverTarget} className="btn btn-primary">
          Discover
        </Link>
      </section>
    </div>
  );
}
