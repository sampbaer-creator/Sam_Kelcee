# Our Wedding — Static Site (tailwind + Firebase placeholder)

This is a static HTML wedding site scaffold. It uses Tailwind via CDN for styling and includes a client-side Firebase placeholder for RSVP submissions.

Getting started

1. Open `our-wedding-static` in a simple static server (or just open `index.html` in a browser).

2. To enable Firestore writes, replace the config in `js/firebase.js` with your project's Firebase config and ensure Firestore rules allow writes for your workflow (or secure accordingly).

Features
- Pages: `index.html`, `rsvp.html`, `registry.html`, `event-details.html`.
- RSVP form saves to Firestore when configured, otherwise falls back to `localStorage`.
- Countdown timer on the home page.

Deploy
- You can host this as static files on GitHub Pages, Firebase Hosting, Netlify, or Vercel.

Notes
- The Firebase setup is intentionally minimal for quick prototyping. For production, secure Firestore with appropriate rules and consider server-side validation or Cloud Functions for email delivery or CSV export.

Feel free to tell me any text, images, or links you want customized (names, wedding date, registry links, map locations), and I’ll update them.