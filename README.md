# Achievement Unlocked!

A real-world achievement tracker.  Includes public display of achievement lists, and forum signature images.

## Requirements

* [Medoo](http://medoo.in)

## Database schema

```sql
CREATE TABLE "users" (
  "uid" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user" text NOT NULL,
  "pass" text NOT NULL
);
CREATE TABLE "tasks" (
  "tid" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "uid" integer NOT NULL,
  "name" text NOT NULL,
  "desc" text NOT NULL,
  "cur" integer NOT NULL,
  "tot" integer NOT NULL,
  FOREIGN KEY ("uid") REFERENCES "users" ("uid")
);
```
