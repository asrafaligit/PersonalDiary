<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <!-- Link your CSS file here -->
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
      }

      header {
        background-color: #333;
        color: white;
        padding: 1rem;
      }

      nav {
        margin-left: 30rem;
      }
      nav button {
        background-color: #555;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        margin-right: 1rem;
        cursor: pointer;
      }

      a {
        color: white;
        text-decoration: none;
      }
      main {
        padding: 2rem;
      }

      .diary-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
      }

      .diary {
        border: 1px solid #ccc;
        padding: 20px;
        margin-bottom: 20px;
      }

      .navigation-buttons {
        margin-top: 20px;
        text-align: center;
      }

      .navigation-buttons button {
        background-color: #555;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        margin-right: 1rem;
        cursor: pointer;
      }

      .navigation-buttons button:hover {
        background-color: #333;
      }
    </style>

    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
      <nav>
        <button id="homeBtn"><a href="homePage.php">Home</a></button>
        <button id="reminderbtn"><a href="reminderPage.php">Reminder</a></button>
        <button id="reminderbtn"><a href="displayReminder.php">Your Reminder</a></button>
        <button id="dateBtn"><a href="#">Your Diaries</a></button>
        <button id="accountBtn"><a href="profile.php">Profile</a></button>
      </nav>
    </header>
    <div class="diary-container">
        <h2>Saved Diaries</h2>
        <div id="diaryEntries">
            <!-- Diary entries will be displayed here -->
        </div>
    </div>

    <!-- JavaScript to fetch and display diary entries -->
    <script>
        // Fetch and display diary entries when the page loads
        document.addEventListener('DOMContentLoaded', function () {
            fetchDiaryEntries();
        });

        // Function to fetch and display diary entries
        function fetchDiaryEntries() {
            fetch('fetch_diary_entries.php')
                .then(response => response.json())
                .then(data => {
                    const diaryEntries = document.getElementById('diaryEntries');
                    // Clear existing entries
                    diaryEntries.innerHTML = '';
                    // Loop through each entry and display it
                    data.forEach(entry => {
                        const entryElement = document.createElement('div');
                        entryElement.classList.add('diary');
                        entryElement.innerHTML = `
                            <h3>${entry.date}</h3>
                            <h3>${entry.title}</h3>
                            <p>${entry.content}</p>
                            <button onclick="editDiary(${entry.id})">Edit</button>
                            <button onclick="deleteDiary(${entry.id})">Delete</button>
                        `;
                        diaryEntries.appendChild(entryElement);
                    });
                })
                .catch(error => console.error('Error fetching diary entries:', error));
        }

        // Function to edit a diary entry
        function editDiary(id) {
            // Redirect to the edit page with the entry ID
            window.location.href = `edit_diary.php?id=${id}`;
        }

        // Function to delete a diary entry
        function deleteDiary(id) {
            if (confirm('Are you sure you want to delete this diary entry?')) {
                fetch(`delete_diary.php?id=${id}`, { method: 'DELETE' })
                    .then(response => {
                        if (response.ok) {
                            // Reload the page to reflect the changes
                            window.location.reload();
                        } else {
                            console.error('Failed to delete diary entry');
                        }
                    })
                    .catch(error => console.error('Error deleting diary entry:', error));
            }
        }
    </script>
</body>
</html>
