<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global News Hub - Cybersecurity</title>
  
    <style>
        /* General Body and Container Styles */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .app-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(to right, #2563eb, #1d4ed8); /* from-blue-600 to-blue-800 */
            color: #ffffff; /* text-white */
            padding: 1.5rem; /* p-6 */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-lg */
            border-bottom-left-radius: 0.75rem; /* rounded-b-xl */
            border-bottom-right-radius: 0.75rem; /* rounded-b-xl */
            text-align: center; /* text-center */
        }

        .header h1 {
            font-size: 2.25rem; /* text-4xl */
            font-weight: 800; /* font-extrabold */
            margin-bottom: 0.5rem; /* mb-2 */
        }

        .header p {
            color: #bfdbfe; /* text-blue-200 */
            font-size: 1.125rem; /* text-lg */
        }

        /* Navbar Styles */
        .header .navbar {
            margin-top: 1rem; /* mt-4 */
        }

        .navbar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem; /* space-x-2 */
            font-size: 22px; /* text-lg */
            font-weight: 500; /* font-medium */
        }

        @media (min-width: 768px) { /* md: */
            .navbar ul {
                gap: 1.5rem; /* md:space-x-6 */
            }
        }

        .navbar button {
            padding: 0.5rem 1rem; /* py-2 px-4 */
            border-radius: 0.375rem; /* rounded-md */
            transition: background-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            background-color: transparent;
            color: #ffffff;
            border: none;
            cursor: pointer;
            outline: none;
            font-size: 22px;
        }

        .navbar button:hover {
            background-color: rgba(29, 78, 216, 0.5); /* hover:bg-blue-700 hover:bg-opacity-50 */
        }

        .navbar button.active {
            background-color: #1d4ed8; /* bg-blue-700 */
            color: #ffffff; /* text-white */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-md */
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1; /* flex-grow */
            padding: 1rem; /* p-4 */
            max-width: 1200px; /* container mx-auto */
            margin-left: auto;
            margin-right: auto;
            width: 100%; /* Ensure it takes full width within its max-width */
            box-sizing: border-box; /* Include padding in width calculation */
        }

        /* Loading, Error, No Articles Messages */
        .loading-message, .error-message, .no-articles-message {
            text-align: center;
            padding: 2rem; /* py-8 */
            font-size: 1.25rem; /* text-xl */
            font-weight: 600; /* font-semibold */
        }

        .loading-spinner {
            border: 2px solid transparent;
            border-bottom-color: #2563eb; /* border-b-2 border-blue-600 */
            border-radius: 50%;
            width: 3rem; /* h-12 w-12 */
            height: 3rem;
            animation: spin 1s linear infinite; /* animate-spin */
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 1rem; /* mb-4 */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .error-message {
            background-color: #fee2e2; /* bg-red-100 */
            border: 1px solid #ef4444; /* border border-red-400 */
            color: #b91c1c; /* text-red-700 */
            padding: 0.75rem 1rem; /* px-4 py-3 */
            border-radius: 0.375rem; /* rounded-md */
            position: relative;
        }

        .error-message strong {
            font-weight: 700; /* font-bold */
        }

        .error-message span {
            display: block; /* block */
            margin-left: 0.5rem; /* ml-2 */
        }

        @media (min-width: 640px) { /* sm: */
            .error-message span {
                display: inline; /* sm:inline */
            }
        }

        .no-articles-message {
            color: #4b5563; /* text-gray-600 */
            font-size: 1.125rem; /* text-lg */
        }

        /* Articles Grid */
        .articles-grid {
            display: grid;
            grid-template-columns: 1fr; /* grid-cols-1 */
            gap: 1.5rem; /* gap-6 */
        }

        @media (min-width: 768px) { /* md: */
            .articles-grid {
                grid-template-columns: repeat(2, 1fr); /* md:grid-cols-2 */
            }
        }

        @media (min-width: 1024px) { /* lg: */
            .articles-grid {
                grid-template-columns: repeat(3, 1fr); /* lg:grid-cols-3 */
            }
        }

        /* Article Card */
        .article-card {
            background-color: #ffffff; /* bg-white */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-md */
            overflow: hidden;
            transform: scale(1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            display: flex; /* Use flexbox to ensure image and content stack */
            flex-direction: column; /* Stack image and content vertically */
        }

        .article-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05); /* hover:shadow-xl */
        }

        .article-image {
            width: 100%;
            height: 12rem; /* h-48 */
            object-fit: cover;
        }

        .no-image-placeholder {
            width: 100%;
            height: 12rem; /* h-48 */
            background-color: #dbeafe; /* bg-blue-100 */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6; /* text-blue-500 */
            font-size: 0.875rem; /* text-sm */
        }

        .article-content {
            padding: 1.25rem; /* p-5 */
            flex-grow: 1; /* Allow content to grow and push button to bottom */
            display: flex;
            flex-direction: column;
        }

        .article-content h2 {
            font-size: 1.25rem; /* text-xl */
            font-weight: 600; /* font-semibold */
            color: #1f2937; /* text-gray-800 */
            margin-bottom: 0.5rem; /* mb-2 */
            line-height: 1.25; /* leading-tight */
        }

        .article-content h2 a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease-in-out;
        }

        .article-content h2 a:hover {
            color: #3b82f6; /* hover:text-blue-600 */
        }

        .article-meta {
            font-size: 0.875rem; /* text-sm */
            color: #6b7280; /* text-gray-500 */
            margin-bottom: 0.75rem; /* mb-3 */
        }

        .article-description {
            color: #374151; /* text-gray-700 */
            font-size: 1rem; /* text-base */
            margin-bottom: 1rem; /* mb-4 */
            flex-grow: 1; /* Allow description to take available space */
        }

        .read-more-button {
            display: inline-block;
            background-color: #3b82f6; /* bg-blue-500 */
            color: #ffffff; /* text-white */
            font-weight: 700; /* font-bold */
            padding: 0.5rem 1rem; /* py-2 px-4 */
            border-radius: 0.375rem; /* rounded-md */
            font-size: 0.875rem; /* text-sm */
            text-decoration: none;
            transition: background-color 0.3s ease-in-out;
            border: none;
            cursor: pointer;
            outline: none;
            align-self: flex-start; /* Align button to the start within its flex container */
        }

        .read-more-button:hover {
            background-color: #2563eb; /* hover:bg-blue-600 */
        }

        /* Pagination Controls */
        .pagination-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2rem; /* mt-8 */
            gap: 1rem; /* space-x-4 */
            padding: 1rem; /* p-4 */
            background-color: #ffffff; /* bg-white */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-md */
        }

        .pagination-button {
            background-color: #d1d5db; /* bg-gray-300 */
            color: #1f2937; /* text-gray-800 */
            font-weight: 700; /* font-bold */
            padding: 0.5rem 1rem; /* py-2 px-4 */
            border-radius: 0.375rem; /* rounded-md */
            transition: background-color 0.3s ease-in-out;
            border: none;
            cursor: pointer;
            outline: none;
        }

        .pagination-button:hover:not(:disabled) {
            background-color: #9ca3af; /* hover:bg-gray-400 */
        }

        .pagination-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-button.next {
            background-color: #3b82f6; /* bg-blue-500 */
            color: #ffffff; /* text-white */
        }

        .pagination-button.next:hover:not(:disabled) {
            background-color: #2563eb; /* hover:bg-blue-600 */
        }

        .page-number {
            font-size: 1.125rem; /* text-lg */
            font-weight: 600; /* font-semibold */
            color: #374151; /* text-gray-700 */
        }

        /* Footer Styles */
        .footer {
            background-color: #1f2937; /* bg-gray-800 */
            color: #ffffff; /* text-white */
            text-align: center;
            padding: 1rem; /* p-4 */
            margin-top: 2rem; /* mt-8 */
            border-top-left-radius: 0.75rem; /* rounded-t-xl */
            border-top-right-radius: 0.75rem; /* rounded-t-xl */
        }

        .footer p {
            margin-bottom: 0.25rem;
        }

        .footer a {
            color: #93c5fd; /* text-gray-400, hover:text-blue-400 */
            text-decoration: underline;
        }

        .footer a:hover {
            color: #60a5fa;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header>
            <nav class="navbar">
                <ul>
                    <li>
                        <button id="generalBtn" class="active">General</button>
                    </li>
                    <li>
                        <button id="cybersecurityBtn">Cybersecurity</button>
                    </li>
                    <li>
                        <button id="hacksBtn">Hacks</button>
                    </li>
                    <li>
                        <button id="cyberwarfareBtn">Cyberwarfare</button>
                    </li>
                </ul>
            </nav>
        </header>

        <main class="main-content">
            <div id="loadingMessage" class="loading-message" style="display: none;">
                <div class="loading-spinner"></div>
                Loading news articles...
            </div>

            <div id="errorMessage" class="error-message" style="display: none;" role="alert">
                <strong>Error:</strong>
                <span id="errorText"></span>
            </div>

            <div id="noArticlesMessage" class="no-articles-message" style="display: none;">
                No articles found for your search criteria. Try different keywords or filters.
            </div>

            <div id="articlesGrid" class="articles-grid">
                </div>

            <div id="paginationControls" class="pagination-controls" style="display: none;">
                <button id="previousPageBtn" class="pagination-button" disabled>Previous Page</button>
                <span id="currentPageNumber" class="page-number">Page 1</span>
                <button id="nextPageBtn" class="pagination-button next" style="position: relative; font-size: 20px; top: 20px;" disabled>Next Page</button>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; <span id="currentYear"></span> MS Cyber News. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // IMPORTANT: Replace 'YOUR_NEWSDATA_IO_API_KEY' with your actual API key from newsdata.io
        const NEWS_DATA_API_KEY = 'pub_4661474bad034cc2adf5f9eed07adbb5'; // <--- Ensure your API key is here!

        const API_BASE_URL = 'https://newsdata.io/api/1/latest';

        // Define initial topics for the "General" tab on reload
        const initialGeneralTopics = [
            { query: 'cybersecurity', category: 'technology,science' },
            { query: 'hacks', category: 'technology' },
            { query: 'cyberwarfare', category: 'politics,technology' }
        ];

        // Global state variables
        let currentQuery = '';
        let currentCategory = '';
        let currentLanguage = 'en';
        let currentCountry = '';
        let currentDomain = '';
        let currentNextPage = '';
        let currentPage = 1;
        let activeNavbarButtonId = 'generalBtn'; // Default active button on load

        // DOM elements
        const loadingMessage = document.getElementById('loadingMessage');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const noArticlesMessage = document.getElementById('noArticlesMessage');
        const articlesGrid = document.getElementById('articlesGrid');
        const paginationControls = document.getElementById('paginationControls');
        const previousPageBtn = document.getElementById('previousPageBtn');
        const nextPageBtn = document.getElementById('nextPageBtn');
        const currentPageNumberSpan = document.getElementById('currentPageNumber');
        const navbarButtons = document.querySelectorAll('.navbar button');

        // Function to display messages
        function showMessage(element, message = '') {
            element.style.display = 'block';
            if (element === errorMessage) {
                errorText.textContent = message;
            }
        }

        function hideMessage(element) {
            element.style.display = 'none';
        }

        // Function to render articles
        function renderArticles(articles) {
            articlesGrid.innerHTML = ''; // Clear existing articles
            if (articles.length === 0) {
                showMessage(noArticlesMessage);
                paginationControls.style.display = 'none';
                return;
            }

            hideMessage(noArticlesMessage);
            paginationControls.style.display = 'flex';

            articles.forEach(article => {
                const articleCard = document.createElement('div');
                articleCard.className = 'article-card';
                articleCard.innerHTML = `
                    ${article.image_url ?
                        `<img src="${article.image_url}" alt="${article.title || 'News image'}" class="article-image" onerror="this.onerror=null;this.src='https://placehold.co/600x400/E0E7FF/3B82F6?text=Image+Not+Available';">` :
                        `<div class="no-image-placeholder">No Image Available</div>`
                    }
                    <div class="article-content">
                        <h2>
                            <a href="${article.link}" target="_blank" rel="noopener noreferrer">
                                ${article.title || 'Untitled Article'}
                            </a>
                        </h2>
                        <p class="article-meta">
                            ${article.source_name || 'Unknown Source'}
                            ${article.pubDate ? ` - ${new Date(article.pubDate).toLocaleDateString()}` : ''}
                        </p>
                        <p class="article-description">
                            ${article.description || 'No description available.'}
                        </p>
                        <a href="${article.link}" target="_blank" rel="noopener noreferrer" class="read-more-button">
                            Read More
                        </a>
                    </div>
                `;
                articlesGrid.appendChild(articleCard);
            });
        }

        // Function to fetch news
        async function fetchNews(pageToFetch = '') {
            hideMessage(errorMessage);
            hideMessage(noArticlesMessage);
            showMessage(loadingMessage);
            articlesGrid.innerHTML = ''; // Clear articles while loading
            paginationControls.style.display = 'none'; // Hide pagination during loading

            const params = new URLSearchParams({
                apikey: NEWS_DATA_API_KEY,
                language: currentLanguage,
            });

            if (currentQuery) params.append('q', currentQuery);
            if (currentCategory) params.append('category', currentCategory);
            if (currentCountry) params.append('country', currentCountry);
            if (currentDomain) params.append('domain', currentDomain);
            if (pageToFetch) params.append('page', pageToFetch);

            const url = `${API_BASE_URL}?${params.toString()}`;

            try {
                const response = await fetch(url);

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
                    let msg = `HTTP error! Status: ${response.status} - ${errorData.message || 'Failed to fetch data from Newsdata.io'}`;
                    if (response.status === 429) {
                        msg = `Too many requests (Error 429). You've hit the API rate limit for Newsdata.io's free tier. Please wait a while before making more requests (typically resets daily).`;
                    }
                    throw new Error(msg);
                }

                const data = await response.json();

                if (data.status === 'success' && data.results && data.results.length > 0) {
                    renderArticles(data.results);
                    currentNextPage = data.nextPage || '';
                    nextPageBtn.disabled = !currentNextPage;
                    previousPageBtn.disabled = currentPage === 1;
                    currentPageNumberSpan.textContent = `Page ${currentPage}`;
                } else if (data.status === 'success' && data.results && data.results.length === 0) {
                    renderArticles([]); // Show no articles message
                } else {
                    throw new Error(data.message || 'Newsdata.io API returned an error.');
                }
            } catch (err) {
                console.error('Error fetching news:', err);
                showMessage(errorMessage, err.message);
                articlesGrid.innerHTML = ''; // Clear articles on error
                paginationControls.style.display = 'none'; // Hide pagination on error
            } finally {
                hideMessage(loadingMessage);
            }
        }

        // Navbar click handler
        function handleNavbarClick(newQuery, newCategory, buttonId) {
            currentQuery = newQuery;
            currentCategory = newCategory;
            currentDomain = '';
            currentCountry = '';
            currentLanguage = 'en'; // Reset language for simpler navbar behavior
            currentPage = 1; // Reset to first page for new topic

            // Update active button styling
            navbarButtons.forEach(button => {
                button.classList.remove('active');
            });
            document.getElementById(buttonId).classList.add('active');
            activeNavbarButtonId = buttonId;

            fetchNews(); // Fetch news for the selected topic
        }

        // Event Listeners
        window.onload = function() {
            // Set current year in footer
            document.getElementById('currentYear').textContent = new Date().getFullYear();

            // Initial setup: On page load, set the "General" tab to a random cybersecurity topic
            if (NEWS_DATA_API_KEY === 'YOUR_NEWSDATA_IO_API_KEY' || !NEWS_DATA_API_KEY) {
                showMessage(errorMessage, "Please replace 'YOUR_NEWSDATA_IO_API_KEY' with your actual API key in the JavaScript code.");
                hideMessage(loadingMessage);
                return;
            }

            // Randomly select a topic for the initial "General" view on page load
            const randomIndex = Math.floor(Math.random() * initialGeneralTopics.length);
            const initialTopic = initialGeneralTopics[randomIndex];

            currentQuery = initialTopic.query;
            currentCategory = initialTopic.category;
            activeNavbarButtonId = 'generalBtn'; // Ensure 'General' is active on initial load

            // Manually set the active class for the 'General' button on initial load
            document.getElementById('generalBtn').classList.add('active');

            fetchNews(); // Fetch news for the randomly selected topic

            // Attach navbar button event listeners
            document.getElementById('generalBtn').addEventListener('click', () => handleNavbarClick('cybersecurity', 'technology,science', 'generalBtn'));
            document.getElementById('cybersecurityBtn').addEventListener('click', () => handleNavbarClick('cybersecurity', 'technology', 'cybersecurityBtn'));
            document.getElementById('hacksBtn').addEventListener('click', () => handleNavbarClick('hacks', 'technology', 'hacksBtn')); // This tab explicitly fetches 'hacks'
            document.getElementById('cyberwarfareBtn').addEventListener('click', () => handleNavbarClick('cyberwarfare', 'politics,technology', 'cyberwarfareBtn'));

            // Pagination event listeners
            nextPageBtn.addEventListener('click', () => {
                if (currentNextPage) {
                    currentPage++;
                    fetchNews(currentNextPage);
                    window.scrollTo(0, 0); // Scroll to top on page change
                }
            });

            previousPageBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    // As noted before, Newsdata.io's free API doesn't directly support previous page token.
                    // This alert reminds the user of this limitation for the free tier.
                    alert("Newsdata.io's free API doesn't directly support going back to the previous page using a 'previousPage' token. You can only go forward using the 'nextPage' token. To implement a full back-and-forth pagination, you would need to store a history of 'nextPage' tokens or use a paid plan with more advanced pagination features.");
                    // To actually go back, you'd need to re-fetch the current page number based on stored states or a different API approach.
                    // For now, it will simply display the alert and won't fetch a previous page.
                }
            });
        };
    </script>
</body>

<script src="./js/index.js"></script>
</html>