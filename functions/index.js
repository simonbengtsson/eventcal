import {onRequest} from "firebase-functions/v2/https"
import logger from "firebase-functions/logger"
import fs from "fs/promises"
import { generateCalendar } from "./app.js"

export const helloWorld = onRequest((_, response) => {
  logger.info("Hello logs!", {structuredData: true});
  response.send("Hello from Firebase!");
});

export const app = onRequest(async (request, response) => {
  try {
    if (request.query.calendar) {
      const calendar = request.query.calendar
      const isBase64 = Boolean(request.query.base64)
      const statuses = request.query.status || ''
      const ical = await generateCalendar(calendar, isBase64, statuses)
      response.header('Content-Type', 'text/calendar; charset=utf-8')
      response.header('Content-Disposition', 'attachment; filename=calendar.ics')
      response.send(ical);
    } else {
      const content = await fs.readFile('index.html')
      response.send(content.toString());
    }
  } catch(error) {
    logger.error('Could not generate home page or calendar', error)
    response.status(500).send('<h1>500 Oops</h1>')
  }
});
