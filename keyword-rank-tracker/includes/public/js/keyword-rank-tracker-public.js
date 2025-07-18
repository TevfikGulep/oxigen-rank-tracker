document.addEventListener('DOMContentLoaded', function() {
    const keywordInput = document.getElementById('keyword-input');
    const trackButton = document.getElementById('track-button');
    const resultsContainer = document.getElementById('results-container');

    trackButton.addEventListener('click', function() {
        const keyword = keywordInput.value.trim();
        if (keyword) {
            trackKeywordRanking(keyword);
        } else {
            alert('Please enter a keyword to track.');
        }
    });

    function trackKeywordRanking(keyword) {
        // Simulate an API call to get keyword ranking
        fetch(`https://api.example.com/track?keyword=${encodeURIComponent(keyword)}`)
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Error fetching keyword ranking:', error);
                alert('There was an error tracking the keyword ranking. Please try again later.');
            });
    }

    function displayResults(data) {
        resultsContainer.innerHTML = ''; // Clear previous results
        if (data.rank) {
            const resultItem = document.createElement('div');
            resultItem.textContent = `Keyword: ${data.keyword}, Rank: ${data.rank}`;
            resultsContainer.appendChild(resultItem);
        } else {
            resultsContainer.textContent = 'No ranking data available for this keyword.';
        }
    }
});