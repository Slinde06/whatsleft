export default function PhotoBlock({ color = "#6D5B7F", initial, style, className = "" }) {
  return (
    <div
      className={`photo-block ${className}`}
      style={{ background: color, ...style }}
    >
      {initial && <span>{initial}</span>}
    </div>
  );
}
