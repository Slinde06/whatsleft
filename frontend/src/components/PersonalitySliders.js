const AXES = [
  { key: "introvert", left: "Introvert", right: "Extrovert" },
  { key: "analytical", left: "Analytical", right: "Creative" },
  { key: "techSavvy", left: "Tech Savvy", right: "Not Technical" },
  { key: "sporty", left: "Sporty", right: "Un sporty" },
];

export default function PersonalitySliders({ personality }) {
  return (
    <div className="personality-box">
      <p className="personality-title">Personality / Behaviors</p>
      {AXES.map((axis) => (
        <div className="personality-axis" key={axis.key}>
          <div className="personality-labels">
            <span>{axis.left}</span>
            <span>{axis.right}</span>
          </div>
          <div className="personality-track">
            <div
              className="personality-thumb"
              style={{ left: `${personality[axis.key]}%` }}
            />
          </div>
        </div>
      ))}
    </div>
  );
}
