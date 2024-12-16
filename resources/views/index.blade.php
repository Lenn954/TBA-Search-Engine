<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lunar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./assets/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .header h1 {
            font-size: 2.5rem;
            color: #28a745;
        }

        .search input {
            font-size: 1.2rem;
            padding: 10px;
            border: 2px solid #28a745;
            border-radius: 25px;
            outline: none;
            transition: border-color 0.3s ease-in-out;
        }

        .search input:focus {
            border-color: #218838;
        }

        .suggestions, .sentences, .results {
            margin-top: 20px;
        }

        .suggestion-item, .sentence-item, .result-item {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .suggestion-item:hover, .sentence-item:hover, .result-item:hover {
            background-color: #f1f1f1;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .result-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .result-item h2 {
            font-size: 1.5rem;
            color: #333;
        }

        .result-item p {
            font-size: 1rem;
            color: #555;
        }

        .result-item img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border-radius: 10px;
        }

        .result-item video {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div id="appStyle" class="container my-5">
        <div class="text-center header">
            <h1><span class="text-success"><i class="fa-brands fa-envira"></i></span>Lunar<span class="text-success">Search</span></h1>
        </div>

        <div class="row">
            <div class="col-lg-5 mx-auto">
                <form id="searchForm">
                    <div class="mb-3 search">
                        <input type="text" class="form-control" id="kata" maxlength="100" placeholder="Masukkan Kata Kunci...">
                        <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                    </div>
                </form>
                <div id="suggestions" class="suggestions"></div>
                <div id="sentences" class="sentences"></div>
                <div id="results" class="results"></div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        const searchForm = document.getElementById('searchForm');
        const keywordInput = document.getElementById('kata');
        const suggestionsDiv = document.getElementById('suggestions');
        const sentencesDiv = document.getElementById('sentences');
        const resultsDiv = document.getElementById('results');
        let debounceTimeout;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Create suggestion item
        const createSuggestionItem = (text) => {
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('suggestion-item');
            itemDiv.textContent = text;
            itemDiv.onclick = () => {
                keywordInput.value = text; // Set input value to selected suggestion
                suggestionsDiv.innerHTML = ''; // Clear suggestions
                fetchSentences(text); // Fetch related sentences
            };
            return itemDiv;
        };

        // Create sentence item
        const createSentenceItem = (text) => {
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('sentence-item');
            itemDiv.textContent = text;
            itemDiv.onclick = () => {
                sentencesDiv.innerHTML = ''; // Clear sentences
                fetchResults(text); // Fetch related results
            };
            return itemDiv;
        };

        // Create result item (includes image and video)
        const createResultItem = (item) => {
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('result-item');
            itemDiv.innerHTML = `
                <h2>${item.title}</h2>
                <p>${item.description}</p>
                <img src="/storage/${item.foto}" alt="${item.title}">
                <video controls>
                    <source src="/storage/${item.video}" type="video/mp4">
                    Browser Anda tidak mendukung video.
                </video>
            `;
            return itemDiv;
        };

        // Fetch suggestions based on keyword input
        const fetchSuggestions = (keyword) => {
            fetch('/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ keyword })
            })
                .then(response => response.json())
                .then(data => {
                    suggestionsDiv.innerHTML = ''; // Clear previous suggestions
                    sentencesDiv.innerHTML = ''; // Clear sentences
                    resultsDiv.innerHTML = ''; // Clear results

                    if (data.suggestions?.kata?.length > 0) {
                        data.suggestions.kata.forEach(item => {
                            suggestionsDiv.appendChild(createSuggestionItem(item));
                        });
                        suggestionsDiv.classList.add('visible');
                    } else {
                        suggestionsDiv.classList.remove('visible');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    suggestionsDiv.classList.remove('visible');
                });
        };

        // Fetch sentences based on selected suggestion
        const fetchSentences = (keyword) => {
            fetch('/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ keyword })
            })
                .then(response => response.json())
                .then(data => {
                    sentencesDiv.innerHTML = ''; // Clear previous sentences
                    resultsDiv.innerHTML = ''; // Clear results

                    if (data.suggestions?.kalimat?.length > 0) {
                        data.suggestions.kalimat.forEach(item => {
                            sentencesDiv.appendChild(createSentenceItem(item));
                        });
                    } else {
                        sentencesDiv.innerHTML = '<p>Tidak ada kalimat yang ditemukan.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    sentencesDiv.innerHTML = '<p>Error fetching sentences.</p>';
                });
        };

        // Fetch results based on selected sentence
        const fetchResults = (sentence) => {
            fetch('/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ keyword: sentence })
            })
                .then(response => response.json())
                .then(data => {
                    resultsDiv.innerHTML = ''; // Clear previous results

                    if (data.results?.length > 0) {
                        data.results.forEach(item => {
                            resultsDiv.appendChild(createResultItem(item));
                        });
                    } else {
                        resultsDiv.innerHTML = '<p>Tidak ada hasil yang ditemukan.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsDiv.innerHTML = '<p>Error fetching results.</p>';
                });
        };

        // Handle input changes with debounce for suggestions
        keywordInput.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                const keyword = keywordInput.value.trim();
                if (keyword) {
                    fetchSuggestions(keyword);
                } else {
                    suggestionsDiv.classList.remove('visible');
                    sentencesDiv.innerHTML = '';
                    resultsDiv.innerHTML = '';
                }
            }, 300);
        });

        // Prevent default form submission and handle search
        searchForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const keyword = keywordInput.value.trim();
            if (keyword) {
                sentencesDiv.innerHTML = '';
                resultsDiv.innerHTML = '';
                fetchSentences(keyword);
            }
        });
    </script>
</body>

</html>
