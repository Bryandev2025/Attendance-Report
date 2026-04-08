## Student Absence Report System (Backend API)

### Auth
- **Login**: `POST /api/auth/login`
  - body: `{ "email": "...", "password": "..." }`
  - returns: `{ token, user }`
- **Me**: `GET /api/auth/me` (Bearer token)
- **Logout**: `POST /api/auth/logout` (Bearer token)

### Bearer token usage
Send header: `Authorization: Bearer <token>`

### Seeded test accounts (after `php artisan migrate:fresh --seed`)
- Admin: `admin@example.com` / `password`
- Teacher: `teacher@example.com` / `password`
- Student: `student@example.com` / `password`

### Roles
All routes below require auth. Most are role-gated.

### Admin (`role:admin`)
- Users: `GET/POST/PUT/DELETE /api/admin/users`
- School years: `GET/POST/PUT/DELETE /api/admin/school-years`
  - set active: `POST /api/admin/school-years/{id}/set-active`
- Classes: `GET/POST/PUT/DELETE /api/admin/classes`
- Audit logs: `GET /api/admin/audit-logs`
- Absence oversight:
  - `GET /api/admin/absence-reports`
  - `GET /api/admin/absence-reports/{id}`
  - `POST /api/admin/absence-reports/{id}/approve`
  - `POST /api/admin/absence-reports/{id}/reject`

### Teacher (`role:teacher`)
- My classes: `GET /api/teacher/classes`
- Attendance (manual):
  - `POST /api/teacher/attendance/mark`
  - `GET /api/teacher/attendance?class_id=&attendance_date=`
- Attendance sessions (QR / Option B):
  - Open session: `POST /api/teacher/attendance-sessions`
  - Close: `POST /api/teacher/attendance-sessions/{id}/close`
  - QR image: `GET /api/teacher/attendance-sessions/{id}/qr?format=svg|png&size=320`
- Absence reviews:
  - `GET /api/teacher/absence-reports`
  - `POST /api/teacher/absence-reports/{id}/approve`
  - `POST /api/teacher/absence-reports/{id}/reject`

### Student (`role:student`)
- My attendance: `GET /api/student/attendance`
- Absence reports:
  - list: `GET /api/student/absence-reports`
  - submit: `POST /api/student/absence-reports` (supports multipart file uploads)
  - download attachment: `GET /api/student/absence-attachments/{id}`
- Attendance session check-in:
  - `POST /api/student/attendance-sessions/check-in`
  - body: `{ "qr_payload": "SARS_ATTENDANCE_SESSION:..." }`

### Common status codes
- `200/201`: ok/created
- `401`: unauthenticated (missing/invalid token)
- `403`: forbidden (wrong role / ownership)
- `422`: validation/business rule failure
- `429`: throttled (rate limited)

