import { generateCalendar } from "./app"

export default {
  async fetch(request: Request) {
    console.log("fetch", request.url)
    const url = new URL(request.url)
    try {
      if (url.searchParams.get("calendar")) {
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
      return new Response("Not Found 404", { status: 404 })
    } catch (error) {
      console.error("Could not generate home page or calendar", error)
      return new Response("<h1>500 Oops</h1>", { status: 500 })
    }
  },
}
