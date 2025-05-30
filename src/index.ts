import { generateCalendar } from "./app"

export default {
  async fetch(request: Request, env: any) {
    const url = new URL(request.url)
    try {
      // Previously the index path was used with calendar param. This was
      // moved to /calendar path to support matching the index.html file
      // without having to run the worker.
      if (url.pathname === "/calendar" || url.searchParams.get("calendar")) {
        const calendar = url.searchParams.get("calendar")!
        const isBase64 = Boolean(url.searchParams.get("base64"))
        const statuses = url.searchParams.get("status") || ""

        const ical = await generateCalendar(calendar, isBase64, statuses)
        return new Response(ical, {
          headers: {
            "Content-Type": "text/calendar; charset=utf-8",
            "Content-Disposition": "attachment; filename=calendar.ics",
          },
        })
      }
      return env.ASSETS.fetch(request)
    } catch (error) {
      console.error("Could not generate home page or calendar", error)
      return new Response("<h1>500 Oops</h1>", { status: 500 })
    }
  },
}
