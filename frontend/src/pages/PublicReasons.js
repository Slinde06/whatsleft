import { useApp } from "../context/AppContext";
import PhotoBlock from "../components/PhotoBlock";

export default function PublicReasons() {
  const { profiles, log } = useApp();

  const rows = profiles.map((profile) => {
    const entries = log.filter((e) => e.profileId === profile.id);
    const likes = entries.filter((e) => e.type === "like").length;
    const dislikes = entries.filter((e) => e.type === "dislike").length;
    const unmatches = entries.filter((e) => e.type === "unmatch").length;
    const mostRecentUnmatch = [...entries]
      .filter((e) => e.type === "unmatch")
      .sort((a, b) => b.timestamp - a.timestamp)[0];
    const mostRecentPass = [...entries]
      .filter((e) => e.type === "dislike")
      .sort((a, b) => b.timestamp - a.timestamp)[0];
    const reason =
      mostRecentUnmatch?.longReason ||
      mostRecentPass?.longReason ||
      "\u2014";

    return {
      profile,
      unmatches,
      rejections: dislikes,
      likes,
      dislikes,
      reason,
      hasActivity: entries.length > 0,
    };
  });

  return (
    <div className="leaderboard-page">
      <h1>Public reasons</h1>
      <p className="split-sub">
        Every match, unmatch, and pass — and the reason behind it — stays on
        the record, permanently.
      </p>

      <div className="leaderboard-table-wrap">
        <table className="leaderboard-table">
          <thead>
            <tr>
              <th>Img:</th>
              <th>Name:</th>
              <th>Unmatches:</th>
              <th>Rejections:</th>
              <th>Likes:</th>
              <th>Dislikes:</th>
              <th>Reason for most recent decision:</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((row, i) => (
              <tr key={row.profile.id} className={i % 2 === 0 ? "row-even" : "row-odd"}>
                <td>
                  <PhotoBlock color={row.profile.color} className="table-thumb" />
                </td>
                <td>{row.profile.name}</td>
                <td>{row.unmatches}</td>
                <td>{row.rejections}</td>
                <td>{row.likes}</td>
                <td>{row.dislikes}</td>
                <td className="reason-cell">{row.hasActivity ? row.reason : "No history yet"}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
