import { render, screen } from "@testing-library/react";
import App from "./App";

test("renders the What's Left brand in the navbar", () => {
  render(<App />);
  const brandEl = screen.getByText(/What’s Left/i);
  expect(brandEl).toBeInTheDocument();
});
