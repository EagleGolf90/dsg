<?php
/**
 * Fetch attendees in the order used to assign their sequential IDs.
 */
function fetch_attendee_ids($conn) {
  $sql = "
    SELECT reg_id, first_name, last_name, submitted_at, sort_order
    FROM (
      SELECT r.id AS reg_id, r.first_name, r.last_name, r.submitted_at, 0 AS sort_order
      FROM registrations r
      UNION ALL
      SELECT ro.registration_id AS reg_id, ro.first_name, ro.last_name, r.submitted_at, ro.id AS sort_order
      FROM registrations_others ro
      JOIN registrations r ON ro.registration_id = r.id
    ) combined
    ORDER BY submitted_at ASC, reg_id ASC, sort_order ASC
  ";

  $result = $conn->query($sql);
  if (!$result) {
    throw new RuntimeException('Unable to fetch attendee IDs: ' . $conn->error);
  }

  return $result;
}
