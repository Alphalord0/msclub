   <style>
        .app-container {
            display: flex;
            flex-direction: column;
        }

        /* Header Styles */
        .heade {
            color: #ffffff; /* text-white */
            padding: 1rem; /* p-6 */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-lg */
            border-bottom-left-radius: 0.75rem; /* rounded-b-xl */
            border-bottom-right-radius: 0.75rem; /* rounded-b-xl */
            text-align: center; /* text-center */
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        .heade h1 {
            font-size: 2.25rem; /* text-4xl */
            font-weight: 800; /* font-extrabold */
            margin-bottom: 0.5rem; /* mb-2 */
        }

        .heade p {
            color: #bfdbfe; /* text-blue-200 */
            font-size: 1.125rem; /* text-lg */
            margin-bottom: 1rem; /* Added margin for search bar */
        }

        /* Navbar Styles */
        .navbar {
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
            font-size: 1.125rem; /* text-lg */
            font-weight: 500; /* font-medium */
            margin-bottom: 1rem; /* Added margin for search bar */
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
            font-size: 1.125rem;
        }

        .navbar button:hover {
            background-color: rgba(29, 78, 216, 0.5); /* hover:bg-blue-700 hover:bg-opacity-50 */
        }

        .navbar button.active {
            background-color: #1d4ed8; /* bg-blue-700 */
            color: #ffffff; /* text-white */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-md */
        }
        /* Style for disabled buttons */
        .navbar button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background-color: rgba(29, 78, 216, 0.3); /* Slightly darker for disabled */
        }
        .navbar button:disabled:hover {
            background-color: rgba(29, 78, 216, 0.3); /* No change on hover for disabled */
        }


        /* Search Bar Styles */
        .search-bar {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .search-bar input[type="text"] {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #bfdbfe; /* Light blue border */
            width: 100%;
            max-width: 400px;
            font-size: 1rem;
            background-color: rgba(255, 255, 255, 0.9); /* Slightly transparent white */
            color: #1f2937;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .search-bar input[type="text"]::placeholder {
            color: #60a5fa; /* Lighter blue placeholder */
        }

        .search-bar input[type="text"]:focus {
            outline: none;
            border-color: #93c5fd; /* Lighter blue on focus */
            box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.5); /* Blue glow on focus */
        }

        .search-bar button {
            background-color: #22c55e; /* Green search button */
            color: white;
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .search-bar button:hover {
            background-color: #16a34a; /* Darker green on hover */
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

        /* Videos Grid */
        .videos-grid { /* Renamed from articles-grid */
            display: grid;
            grid-template-columns: 1fr; /* grid-cols-1 */
            gap: 1.5rem; /* gap-6 */
        }

        @media (min-width: 768px) { /* md: */
            .videos-grid {
                grid-template-columns: repeat(2, 1fr); /* md:grid-cols-2 */
            }
        }

        @media (min-width: 1024px) { /* lg: */
            .videos-grid {
                grid-template-columns: repeat(3, 1fr); /* lg:grid-cols-3 */
            }
        }

        /* Video Card */
        .video-card { /* Renamed from article-card */
            background-color: #ffffff; /* bg-white */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-md */
            overflow: hidden;
            transform: scale(1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out, background-color 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .video-card:hover {
            transform: scale(1.03); /* Slightly reduced hover scale */
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15), 0 3px 5px rgba(0, 0, 0, 0.08); /* Adjusted shadow */
        }

        .video-thumbnail-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio (height / width * 100%) */
            overflow: hidden;
        }

        .video-thumbnail {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image-placeholder {
            width: 100%;
            height: 100%; /* Fill the container */
            background-color: #dbeafe; /* bg-blue-100 */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6; /* text-blue-500 */
            font-size: 0.875rem; /* text-sm */
            position: absolute;
            top: 0;
            left: 0;
        }

        .video-content { /* Renamed from article-content */
            padding: 1.25rem; /* p-5 */
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .video-content h2 { /* Renamed from article-content h2 */
            font-size: 1.25rem; /* text-xl */
            font-weight: 600; /* font-semibold */
            color: #1f2937; /* text-gray-800 */
            margin-bottom: 0.5rem; /* mb-2 */
            line-height: 1.25; /* leading-tight */
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            line-clamp: 2;
            -webkit-line-clamp: 2; /* Limit to 2 lines */
            -webkit-box-orient: vertical;
            transition: color 0.3s ease;
        }

        .video-content h2 a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease-in-out;
        }

        .video-content h2 a:hover {
            color: #3b82f6; /* hover:text-blue-600 */
        }

        .video-meta { /* Renamed from article-meta */
            font-size: 0.875rem; /* text-sm */
            color: #6b7280; /* text-gray-500 */
            margin-bottom: 0.75rem; /* mb-3 */
            transition: color 0.3s ease;
        }

        .video-description { /* Renamed from article-description */
            color: #374151; /* text-gray-700 */
            font-size: 1rem; /* text-base */
            margin-bottom: 1rem; /* mb-4 */
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            line-clamp: 2;
            -webkit-line-clamp: 3; /* Limit to 3 lines */
            -webkit-box-orient: vertical;
            transition: color 0.3s ease;
        }

        .watch-button { /* Renamed from read-more-button */
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
            align-self: flex-start;
            margin-top: auto; /* Push button to bottom */
        }

        .watch-button:hover:not(:disabled) {
            background-color: #2563eb; /* hover:bg-blue-600 */
        }
        .watch-button:disabled {
            background-color: #9ca3af; /* gray-400 */
            cursor: not-allowed;
            opacity: 0.7;
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
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
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
            transition: color 0.3s ease;
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
            transition: background-color 0.3s ease;
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

        /* Theme Toggler */
        .theme-toggler {
            position: fixed;
            bottom: 20px;
            right: 90px;
            z-index: 1000;
            display: flex;
            gap: 10px;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px 15px;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        body.dark .theme-toggler {
            background-color: rgba(45, 55, 72, 0.8);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .theme-toggler button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #d1d5db;
            background-color: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .theme-toggler #moon {
            background-color: #374151; /* Dark gray */
            color: white;
        }
        .theme-toggler #sun {
            background-color: #fcd34d; /* Yellow */
            color: #1f2937; /* Dark text */
            display: none; /* Hidden by default */
        }
        body.dark .theme-toggler #moon {
            display: none;
        }
        body.dark .theme-toggler #sun {
            display: flex; /* Show sun in dark mode */
        }

        /* Message Box for Restrictions */
        .message-box-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black overlay */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000; /* Above everything else */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .message-box-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .message-box {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 0.75rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .message-box h3 {
            font-size: 1.5rem;
            color: #ef4444; /* Red for warning */
            margin-bottom: 15px;
        }
        .message-box p {
            font-size: 1rem;
            color: #374151;
            margin-bottom: 20px;
        }
        .message-box button {
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.2s ease;
        }
        .message-box button:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header class="heade">
            <nav class="navbar">
                <ul>
                    <li>
                        <button id="basicsBtn" class="active">Cybersecurity Basics</button>
                    </li>
                    <li>
                        <button id="hackingBtn" disabled>Hacking Tutorials</button>
                    </li>
                    <li>
                        <button id="threatsBtn">Latest Threats</button>
                    </li>
                    <li>
                        <button id="privacyBtn">Privacy & Security</button>
                    </li>
                </ul>
            </nav>
            <div class="search-bar">
                <input type="text" id="searchVideoInput" placeholder="Search for cybersecurity videos...">
                <button id="searchVideoBtn">Search</button>
            </div>
        </header>

        <main class="main-content">
            <div id="loadingMessage" class="loading-message" style="display: none;">
                <div class="loading-spinner"></div>
                Loading videos...
            </div>

            <div id="errorMessage" class="error-message" style="display: none;" role="alert">
                <strong>Error:</strong>
                <span id="errorText"></span>
            </div>

            <div id="noVideosMessage" class="no-articles-message" style="display: none;">
                No videos found for your criteria. Try different keywords or categories.
            </div>

            <div id="videosGrid" class="videos-grid">
                <!-- Video cards will be dynamically inserted here -->
            </div>

            <div id="paginationControls" class="pagination-controls" style="display: none;">
                <button id="previousPageBtn" class="pagination-button" disabled>Previous</button>
                <span id="currentPageNumber" class="page-number">Page 1</span>
                <button id="nextPageBtn" class="pagination-button next" style="position: relative; font-size: 20px; top: 20px;" disabled>Next</button>
            </div>
        </main>

        <footer class="footer">
            <p>© <span id="currentYear"></span> MS Cybersecurity Videos Hub. All rights reserved.</p>
            <p>Powered by <a href="https://developers.google.com/youtube/v3" target="_blank" rel="noopener noreferrer">YouTube Data API v3</a></p>
        </footer>

       
    </div>

    <!-- Custom Message Box for Restrictions -->
    <div id="messageBoxOverlay" class="message-box-overlay">
        <div class="message-box">
            <h3>Access Restricted</h3>
            <p id="messageBoxText">This website is restricted. Please choose another link.</p>
            <button id="messageBoxCloseBtn">Okay</button>
        </div>
    </div>
<script src="./js/index.js"></script>
    <script>
        // IMPORTANT: Replace 'YOUR_YOUTUBE_API_KEY' with your actual API key from Google Cloud Console
        const YOUTUBE_API_KEY = 'AIzaSyDY6zmulw7A9bWcpMoIyZUAGesUf2N08GM'; // <--- Get your API key from Google Cloud Console!
        const YOUTUBE_API_BASE_URL = 'https://www.googleapis.com/youtube/v3/search';

        // List of blocked domains (add more as needed)
        // This list is for external websites linked from video descriptions, not for filtering YouTube video content itself.
        const blockedDomains = [
            'example.com', // Placeholder for a blocked site
            'badsite.net',
            'anotherbadsite.org'
        ];

        // Global state variables for YouTube API
        let currentSearchQuery = '';
        let currentOrder = 'relevance'; // Default order
        let currentNextPageToken = '';
        let currentPrevPageToken = '';
        let currentPage = 1;
        let pageTokensHistory = [null]; // Stores page tokens to navigate back and forth

        // DOM elements
        const loadingMessage = document.getElementById('loadingMessage');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const noVideosMessage = document.getElementById('noVideosMessage');
        const videosGrid = document.getElementById('videosGrid');
        const paginationControls = document.getElementById('paginationControls');
        const previousPageBtn = document.getElementById('previousPageBtn');
        const nextPageBtn = document.getElementById('nextPageBtn');
        const currentPageNumberSpan = document.getElementById('currentPageNumber');
        const navbarButtons = document.querySelectorAll('.navbar button');

        const messageBoxOverlay = document.getElementById('messageBoxOverlay');
        const messageBoxText = document.getElementById('messageBoxText');
        const messageBoxCloseBtn = document.getElementById('messageBoxCloseBtn');

        // Search bar elements
        const searchVideoInput = document.getElementById('searchVideoInput');
        const searchVideoBtn = document.getElementById('searchVideoBtn');


        // Function to display messages (error/loading/no videos)
        function showMessage(element, message = '') {
            element.style.display = 'block';
            if (element === errorMessage) {
                errorText.textContent = message;
            }
        }

        function hideMessage(element) {
            element.style.display = 'none';
        }

        // Function to show the custom message box
        function showCustomMessageBox(message) {
            messageBoxText.textContent = message;
            messageBoxOverlay.classList.add('show');
        }

        // Function to hide the custom message box
        function hideCustomMessageBox() {
            messageBoxOverlay.classList.remove('show');
        }

        // Check if a URL belongs to a blocked domain
        function isWebsiteBlocked(url) {
            try {
                const urlObj = new URL(url);
                const hostname = urlObj.hostname;
                // Check if hostname is in blockedDomains or if it's a subdomain of a blocked domain
                return blockedDomains.some(blockedDomain => {
                    return hostname === blockedDomain || hostname.endsWith('.' + blockedDomain);
                });
            } catch (e) {
                console.error("Invalid URL for blocking check:", url, e);
                return false; // Treat invalid URLs as not blocked
            }
        }

        // Function to render video cards
        function renderVideos(videos) {
            videosGrid.innerHTML = ''; // Clear existing videos
            if (videos.length === 0) {
                showMessage(noVideosMessage);
                paginationControls.style.display = 'none';
                return;
            }

            hideMessage(noVideosMessage);
            paginationControls.style.display = 'flex';

            videos.forEach(video => {
                const videoId = video.id.videoId;
                // Use default thumbnail if high quality is not available
                const thumbnailUrl = video.snippet.thumbnails.high?.url || video.snippet.thumbnails.medium?.url || video.snippet.thumbnails.default?.url;
                const title = video.snippet.title || 'Untitled Video';
                const channelTitle = video.snippet.channelTitle || 'Unknown Channel';
                const publishedAt = video.snippet.publishedAt ? new Date(video.snippet.publishedAt).toLocaleDateString() : '';
                const description = video.snippet.description || 'No description available.';

                const videoUrl = `https://www.youtube.com/watch?v=${videoId}`; // Correct YouTube watch URL
                const isBlocked = isWebsiteBlocked(videoUrl); // This check is for external links, not the YouTube video itself.

                const videoCard = document.createElement('div');
                videoCard.className = 'video-card';
                videoCard.innerHTML = `
                    <div class="video-thumbnail-container">
                        ${thumbnailUrl ?
                            `<img src="${thumbnailUrl}" alt="${title}" class="video-thumbnail" onerror="this.onerror=null;this.src='https://placehold.co/600x337/E0E7FF/3B82F6?text=Video+Not+Available';">` :
                            `<div class="no-image-placeholder">Video Thumbnail Not Available</div>`
                        }
                    </div>
                    <div class="video-content">
                        <h2>
                            <a href="${isBlocked ? '#' : videoUrl}" target="_blank" rel="noopener noreferrer" ${isBlocked ? 'class="blocked-link" title="This content is restricted"' : ''}>
                                ${title}
                            </a>
                        </h2>
                        <p class="video-meta">
                            ${channelTitle} ${publishedAt ? ` - ${publishedAt}` : ''}
                        </p>
                        <p class="video-description">
                            ${description}
                        </p>
                        <a href="${isBlocked ? '#' : videoUrl}" target="_blank" rel="noopener noreferrer" class="watch-button" ${isBlocked ? 'disabled' : ''}>
                            ${isBlocked ? 'Restricted' : 'Watch Video'}
                        </a>
                    </div>
                `;
                videosGrid.appendChild(videoCard);
            });
        }

        // Function to fetch videos from YouTube Data API
        async function fetchVideos(pageToken = '') {
            hideMessage(errorMessage);
            hideMessage(noVideosMessage);
            showMessage(loadingMessage);
            videosGrid.innerHTML = ''; // Clear videos while loading
            paginationControls.style.display = 'none'; // Hide pagination during loading

            if (YOUTUBE_API_KEY === 'YOUR_YOUTUBE_API_KEY' || !YOUTUBE_API_KEY) {
                showMessage(errorMessage, "Please replace 'YOUR_YOUTUBE_API_KEY' with your actual API key in the JavaScript code.");
                hideMessage(loadingMessage);
                return;
            }

            const params = new URLSearchParams({
                key: YOUTUBE_API_KEY,
                part: 'snippet',
                q: currentSearchQuery,
                type: 'video',
                maxResults: 9, // Number of videos per page
                order: currentOrder,
                relevanceLanguage: 'en',
                safeSearch: 'strict' // <--- Added safeSearch parameter set to 'strict'
            });

            if (pageToken) {
                params.append('pageToken', pageToken);
            }

            const url = `${YOUTUBE_API_BASE_URL}?${params.toString()}`;

            try {
                const response = await fetch(url);

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ error: { message: 'Unknown error' } }));
                    let msg = `HTTP error! Status: ${response.status} - ${errorData.error?.message || 'Failed to fetch data from YouTube API'}`;
                    if (response.status === 403 && errorData.error?.errors?.[0]?.reason === 'quotaExceeded') {
                        msg = `YouTube API quota exceeded. You've hit the daily limit. Please try again tomorrow.`;
                    }
                    throw new Error(msg);
                }

                const data = await response.json();

                if (data.items && data.items.length > 0) {
                    renderVideos(data.items);
                    currentNextPageToken = data.nextPageToken || '';
                    currentPrevPageToken = data.prevPageToken || '';

                    nextPageBtn.disabled = !currentNextPageToken;
                    previousPageBtn.disabled = currentPage === 1; // Always disable if on first page

                    currentPageNumberSpan.textContent = `Page ${currentPage}`;
                } else {
                    renderVideos([]); // Show no videos message
                }
            } catch (err) {
                console.error('Error fetching videos:', err);
                showMessage(errorMessage, err.message);
                videosGrid.innerHTML = ''; // Clear videos on error
                paginationControls.style.display = 'none'; // Hide pagination on error
            } finally {
                hideMessage(loadingMessage);
            }
        }

        // Navbar click handler
        function handleNavbarClick(newQuery, newOrder, buttonId) {
            const clickedButton = document.getElementById(buttonId);
            if (clickedButton && clickedButton.disabled) {
                console.log(`Button "${clickedButton.textContent}" is disabled.`);
                return; // Do nothing if the button is disabled
            }

            currentSearchQuery = newQuery;
            currentOrder = newOrder;
            currentPage = 1; // Reset to first page for new topic
            pageTokensHistory = [null]; // Reset page token history

            // Update active button styling
            navbarButtons.forEach(button => {
                button.classList.remove('active');
            });
            if (clickedButton) {
                clickedButton.classList.add('active');
            }


            // Clear search input when a category button is clicked
            searchVideoInput.value = '';

            fetchVideos(); // Fetch videos for the selected topic
        }

        // Search button click handler
        function handleSearchClick() {
            const query = searchVideoInput.value.trim();
            if (query) {
                currentSearchQuery = query;
                currentOrder = 'relevance'; // Reset order to relevance for new searches
                currentPage = 1; // Reset to first page for new search
                pageTokensHistory = [null]; // Reset page token history

                // Deactivate all navbar category buttons
                navbarButtons.forEach(button => {
                    button.classList.remove('active');
                });

                fetchVideos();
            } else {
                // If search input is empty, maybe revert to default category or show a message
                // For now, let's just do nothing if empty search
                console.log("Search input is empty.");
            }
        }


        // Event Listeners
        window.onload = function() {
            // Set current year in footer
            document.getElementById('currentYear').textContent = new Date().getFullYear();


            // Initial fetch for "Cybersecurity Basics"
            handleNavbarClick('cybersecurity basics', 'relevance', 'basicsBtn');

            // Attach navbar button event listeners
            document.getElementById('basicsBtn').addEventListener('click', () => handleNavbarClick('cybersecurity basics', 'relevance', 'basicsBtn'));
            document.getElementById('hackingBtn').addEventListener('click', () => handleNavbarClick('ethical hacking tutorials', 'viewCount', 'hackingBtn'));
            document.getElementById('threatsBtn').addEventListener('click', () => handleNavbarClick('latest cybersecurity threats', 'date', 'threatsBtn'));
            document.getElementById('privacyBtn').addEventListener('click', () => handleNavbarClick('online privacy and security tips', 'relevance', 'privacyBtn'));

            // Attach search button event listener
            searchVideoBtn.addEventListener('click', handleSearchClick);

            // Allow pressing Enter in the search input to trigger search
            searchVideoInput.addEventListener('keyup', (event) => {
                if (event.key === 'Enter') {
                    handleSearchClick();
                }
            });

            // Pagination event listeners
            nextPageBtn.addEventListener('click', () => {
                if (currentNextPageToken) {
                    currentPage++;
                    pageTokensHistory.push(currentNextPageToken); // Store current next token for potential back navigation
                    fetchVideos(currentNextPageToken);
                    window.scrollTo(0, 0); // Scroll to top on page change
                }
            });

            previousPageBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    pageTokensHistory.pop(); // Remove current token
                    const prevToken = pageTokensHistory[pageTokensHistory.length - 1]; // Get the token for the previous page
                    fetchVideos(prevToken);
                    window.scrollTo(0, 0); // Scroll to top on page change
                }
            });

            

            // Event listener for the custom message box close button
            messageBoxCloseBtn.addEventListener('click', hideCustomMessageBox);

            // Global click listener to intercept all link clicks
            document.addEventListener('click', function(event) {
                let target = event.target;
                // Traverse up the DOM tree to find an <a> tag or a button that acts as a link
                while (target && target !== document.body) {
                    if (target.tagName === 'A' || (target.tagName === 'BUTTON' && target.classList.contains('watch-button'))) {
                        const href = target.getAttribute('href');
                        // Only intercept if href is not '#' and it's not already disabled
                        if (href && href !== '#' && !target.disabled) {
                            if (isWebsiteBlocked(href)) {
                                event.preventDefault(); // Stop the navigation
                                showCustomMessageBox('This website is restricted. Please choose another link.');
                                // Optionally, disable the button/link visually if not already done
                                target.disabled = true;
                                if (target.tagName === 'A') {
                                    target.style.pointerEvents = 'none'; // Make link unclickable
                                    target.style.opacity = '0.7';
                                    target.style.cursor = 'not-allowed';
                                }
                            }
                        }
                        break; // Stop after finding the relevant element
                    }
                    target = target.parentNode;
                }
            });
        };
    </script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit-id.js" crossorigin="anonymous"></script>
    <!-- Ensure you replace 'your-font-awesome-kit-id.js' with your actual Font Awesome Kit URL -->
</body>
</html>
