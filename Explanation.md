# Explanation

## 1. Problem Statement

Here's what I understood from the user story: As a website owner, you want to know which links people actually see (not just which are on the page) when they land on your homepage. Specifically, you care about the links that are visible right away—above the fold—so you can make smart decisions about your layout and what you want visitors to click on. I wanted to build something that gives you that insight, in a way that's easy to use and maintain.

## 2. How I Designed the Solution

I broke the problem into a few clear parts:

- **Frontend (JavaScript):**
  - I inject a JS file on the homepage. When the page loads, it checks every link (`<a>`) and figures out if at least half of it is visible in the browser window. (I picked 50% as a good balance—if you see half a link, you probably notice it.)
  - The script grabs the URLs of those links and the screen size, then sends that info to the server using WordPress's REST API. I use a nonce for security, so only real site visitors can send data.

- **Backend (WordPress):**
  - I set up a custom REST endpoint to receive the data. When it comes in, I store it in a custom database table, along with the time and screen size. I use a custom table so I don't clutter up WordPress's built-in tables, and so it's easy to manage and clean up.
  - Every day, a scheduled task (WP Cron) deletes any data older than 7 days. This keeps things tidy and ensures you're always looking at recent trends.

- **Admin Page:**
  - I added a simple page under Tools in the WP admin. It shows the 50 most recent visits, with the time, screen size, and the links that were above the fold. No fancy filters or charts—just the raw info you need to spot patterns.
  - When you uninstall the plugin, it cleans up after itself and deletes all its data.

## 3. Why I Made These Choices

- **Why a WordPress plugin?**
  - It's the most natural way to add features to a WP site, and it's easy for non-technical users to install, update, and remove.
- **Why REST API and nonce?**
  - REST is modern, flexible, and decouples the JS from the backend. The nonce keeps things secure.
- **Why a custom table?**
  - It keeps your data separate, makes queries fast, and is easy to drop on uninstall.
- **Why the 50% visibility rule?**
  - It's a practical threshold—if a link is half-visible, it's likely to be noticed. It also works well across devices and browsers.
- **Why keep the admin page simple?**
  - I wanted to deliver value quickly and make it easy to extend later if you want more features.

## 4. How This Solves the User Story

Every time someone visits your homepage, the plugin records which links they could actually see right away. You can check the admin page to see what's getting the most visibility, and use that info to tweak your layout or move important links higher up. The data is always fresh (last 7 days), and you don't have to worry about cleaning it up.

## 5. My Approach & Thought Process

When I get a problem like this, I like to break it into small, testable steps. I started with the JS, making sure I could reliably detect visible links. Then I wired up the backend, focusing on security and performance. I tested each piece as I went, making sure the data flowed all the way from the browser to the database and then to the admin page. I kept the code modular and followed WordPress best practices, so it's easy to maintain and extend.

I chose this direction because it's robust, secure, and fits naturally into the WordPress ecosystem. It's also easy to build on—if you want to add charts, export data, or track other pages, it's all possible without major rewrites.

## 6. Why This Is a Good Solution

- It's easy to use and maintain.
- It's secure and doesn't bloat your database.
- It gives you actionable info, not just raw data.
- It's built the "WordPress way," so it'll play nicely with updates and other plugins.

## Future Improvements

- **PHPCS Warnings:**
  - There are some code style warnings about direct SQL queries, lack of caching, and not using `$wpdb->prepare()` for all queries. With more time, I would refactor the code to address these, especially for queries in the admin page and cleanup routines.
- **Admin Page Features:**
  - If I had more time, I would add filtering, aggregation, and maybe export options to the admin page to make it even more useful for analyzing trends.
- **Testing:**
  - More comprehensive unit and integration tests could be added for even greater reliability.