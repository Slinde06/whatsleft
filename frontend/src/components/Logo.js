export default function Logo({ size = 28, color = "currentColor" }) {
  return (
    <svg
      width={size}
      height={size}
      viewBox="0 0 48 48"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
    >
      <circle cx="6" cy="6" r="2" fill={color} />
      <circle cx="13" cy="2.5" r="1.6" fill={color} />
      <circle cx="20" cy="1" r="1.2" fill={color} />
      <path
        d="M24 44C24 44 4 30.5 4 17.5C4 10.6 9.4 5 16 5C19.7 5 23 6.9 24 10C25 6.9 28.3 5 32 5C38.6 5 44 10.6 44 17.5C44 30.5 24 44 24 44Z"
        stroke={color}
        strokeWidth="3"
        strokeLinejoin="round"
        fill="none"
      />
      <path
        d="M12 22L20 30L36 12"
        stroke={color}
        strokeWidth="3.2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        d="M20 22L24 26L22 28"
        fill={color}
      />
    </svg>
  );
}
