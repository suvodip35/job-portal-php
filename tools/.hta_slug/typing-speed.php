<?php
    $metaTitle = "Online Typing Speed Test Tool - Check WPM & Accuracy Instantly";
    $pageTitle = "Typing Speed Test - Improve Your WPM | FromCampus";
    $pageDescription = "Free Online Typing Speed Test Tool to check your WPM, accuracy, typing speed and performance in real-time. Start typing test instantly and improve your keyboard skills.";
    $keywords = "Typing Test, Typing Speed Test, Online WPM Test, Keyboard Practice, Speed Typing, Accuracy Test, Typing Practice Tool, Free Typing Test";
    $ogImage = "https://fromcampus.com/assets/assets/tools-image/typing-speed.jpg";
    $canonicalUrl = "https://fromcampus.com/tools/typing-speed-test";

    $schema = [
        "@context" => "https://schema.org",
        "@type" => "SoftwareApplication",
        "name" => "Typing Speed Test Tool",
        "operatingSystem" => "All",
        "applicationCategory" => "EducationApplication",
        "description" => "Free Online Typing Speed Test Tool to check WPM and accuracy instantly. Improve typing speed with real-time performance tracking.",
        "url" => $canonicalUrl,
        "image" => $ogImage,
        "publisher" => [
            "@type" => "Organization",
            "name" => "FromCampus"
        ],
        "offers" => [
            "@type" => "Offer",
            "price" => "0",
            "priceCurrency" => "INR"
        ]
    ];


    require_once __DIR__ . '/../../.hta_slug/_header.php';
?>

<div class="mb-6 flex justify-center items-center py-10 transition-all duration-300">

    <div class="w-full max-w-xl bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl transition-all duration-300">

        <h1 class="text-3xl font-bold text-center text-indigo-600 dark:text-indigo-400">
            Typing Speed Test
        </h1>

        <!-- Text to Type -->
        <div
            id="text-to-type"
            class="mt-6 text-lg text-gray-700 dark:text-gray-300 p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg break-words transition-all duration-300">
        </div>

        <!-- User Input -->
        <textarea
            id="user-input"
            rows="4"
            class="mt-4 w-full p-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-300"
            placeholder="Start typing here..."></textarea>

        <!-- Stats Row -->
        <div class="mt-4 flex justify-between text-lg font-semibold">
            <span class="text-indigo-600 dark:text-indigo-400">Time: <span id="timer">0</span>s</span>
            <span class="text-indigo-600 dark:text-indigo-400">WPM: <span id="wpm">0</span></span>
        </div>

        <!-- Accuracy -->
        <div class="mt-3 text-lg text-indigo-600 dark:text-indigo-400">
            Accuracy: <span id="accuracy">0</span>%
        </div>

        <!-- Progress Bar -->
        <div id="progress" class="mt-6 bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
            <div id="progress-bar" class="bg-indigo-600 dark:bg-indigo-500 h-2 rounded-full transition-all" style="width: 0%;"></div>
        </div>

        <!-- Start Button -->
        <button
            onclick="startTest()"
            id="start-btn"
            class="mt-6 w-full p-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-500 transition duration-300">
            Start Test
        </button>

        <!-- Result Message -->
        <div class="mt-4 text-center">
            <span id="result-message" class="text-lg font-semibold text-indigo-600 dark:text-indigo-400 hidden"></span>
        </div>

        <!-- Best WPM -->
        <div class="mt-3 text-center text-sm text-gray-600 dark:text-gray-300">
            Best WPM:
            <span id="best-wpm" class="text-indigo-600 dark:text-indigo-400 font-semibold">0</span>
        </div>
    </div>
</div>
<div class="mt-8 p-6 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
    <h3 class="text-2xl font-bold mb-3 text-gray-900 dark:text-white">
        Typing Speed Test – Usage & Purpose
    </h3>
    <p class="text-gray-700 dark:text-gray-300 mb-2">
        The Typing Speed Test tool helps you measure how fast and accurately you can type in real time. 
        It is ideal for students, office workers, content writers, and anyone who wants to improve 
        their typing skills for exams, jobs, or daily computer usage.
    </p>

    <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300 space-y-1">
        <li>Check your typing speed in Words Per Minute (WPM).</li>
        <li>Track your typing accuracy with real-time error highlighting.</li>
        <li>Measure performance in a 60-second typing session.</li>
        <li>Improve your keyboard typing skills with practice.</li>
        <li>See your best WPM result saved automatically in your browser.</li>
    </ul>

    <h3 class="text-xl font-semibold mt-5 text-gray-900 dark:text-white">
        Why Use This Tool?
    </h3>
    <p class="text-gray-700 dark:text-gray-300">
        Typing speed is an essential skill for computer-based exams, online tests, data entry jobs, 
        freelancing, programming, and office work. This Typing Test Tool helps you practice regularly 
        and track your growth so you can type faster, make fewer mistakes, and work more efficiently 
        on any computer task.
    </p>
</div>


<script>
    const textToTypeElement = document.getElementById('text-to-type');
    const userInputElement = document.getElementById('user-input');
    const timerElement = document.getElementById('timer');
    const wpmElement = document.getElementById('wpm');
    const accuracyElement = document.getElementById('accuracy');
    const progressBarElement = document.getElementById('progress-bar');
    const startButton = document.getElementById('start-btn');
    const resultMessage = document.getElementById('result-message');
    const bestWPMElement = document.getElementById('best-wpm');

    let testText ="The quick brown fox jumps over the lazy dog. This typing test helps measure your speed and accuracy. Keep practicing to improve your performance.";
    
    let time = 0;
    let started = false;
    let correctChars = 0;
    let totalChars = 0;
    let interval;
    let bestWPM = 0;

    function startTest() {
        clearInterval(interval);
        time = 0;
        correctChars = 0;
        totalChars = 0;

        userInputElement.value = "";
        textToTypeElement.innerHTML = testText;

        progressBarElement.style.width = "0%";
        accuracyElement.textContent = "0";
        wpmElement.textContent = "0";

        resultMessage.classList.add("hidden");

        started = true;
        startButton.textContent = "Restart Test";

        interval = setInterval(() => {
            time++;
            timerElement.textContent = time;
            updateProgressBar();

            if (time >= 60) {
                clearInterval(interval);
                finishTest();
            }
        }, 1000);
    }

    function updateProgressBar() {
        const progress = (time / 60) * 100;
        progressBarElement.style.width = progress + "%";
    }

    function finishTest() {
        const wpm = calculateWPM();
        resultMessage.textContent = `Time’s up! Your WPM is: ${wpm}`;
        resultMessage.classList.remove("hidden");

        if (wpm > bestWPM) {
            bestWPM = wpm;
            bestWPMElement.textContent = bestWPM;
        }
    }

    // Typing Input Listener
    userInputElement.addEventListener("input", () => {
        if (!started) return;

        const typedText = userInputElement.value;
        totalChars = typedText.length;

        if (typedText === testText) {
            clearInterval(interval);
            finishTest();
        }

        correctChars = 0;
        for (let i = 0; i < typedText.length; i++) {
            if (typedText[i] === testText[i]) correctChars++;
        }

        const accuracy = (correctChars / totalChars) * 100;
        accuracyElement.textContent = accuracy ? accuracy.toFixed(2) : 0;

        // Highlight Text
        const updatedText = testText
            .split("")
            .map((char, index) => {
                if (typedText[index] === char) {
                    return `<span class="bg-indigo-600 text-white px-1">${char}</span>`;
                } else if (typedText[index] !== undefined) {
                    return `<span class="text-red-500 font-semibold">${char}</span>`;
                }
                return `<span>${char}</span>`;
            })
            .join("");

        textToTypeElement.innerHTML = updatedText;

        wpmElement.textContent = calculateWPM();
    });

    function calculateWPM() {
        if (time === 0) return 0;
        return Math.round((correctChars / 5) / (time / 60));
    }
</script>
