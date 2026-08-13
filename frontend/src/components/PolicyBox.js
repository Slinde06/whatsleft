export default function PolicyBox({ title = "BEFORE YOU JOIN — THE VERDICT POLICY" }) {
  return (
    <div className="policy-box">
      <p className="policy-title">{title}</p>
      <ul>
        <li>You may skip exactly one profile, ever.</li>
        <li>You get exactly one undo, ever — spend it wisely.</li>
        <li>Passing on someone requires a public, detailed reason.</li>
        <li>
          Matching moves your account to a new shared space — you can&rsquo;t
          browse again unless it ends.
        </li>
        <li>Nothing you do here can ever be deleted.</li>
      </ul>
    </div>
  );
}
