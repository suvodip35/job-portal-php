<?php
require_once('_header.php');
require __DIR__ . '/../lib/parsedown-master/Parsedown.php';
$Parsedown = new Parsedown();

$siteTitle = "Saved Jobs — " . APP_NAME;
$metaDesc  = "View your saved jobs on " . APP_NAME;

// Get latest updates (same as homepage)
$latestStmt = $pdo->prepare("SELECT job_id, job_title, job_title_slug, posted_date FROM jobs WHERE status='published' AND posted_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY posted_date DESC LIMIT 5");
$latestStmt->execute();
$latestUpdates = $latestStmt->fetchAll();

// Get saved job slugs from localStorage via AJAX
?>
<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">    
    <section class="max-w-6xl mx-auto px-4 py-10">
        <div class="grid md:grid-cols-4 gap-8">
            <!-- Main content -->
            <div class="md:col-span-3">
                <div class="flex justify-between mb-8 gap-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Saved Jobs</h1>
                    <button onclick="fetchSavedJobs()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm shadow whitespace-nowrap">Refresh</button>
                </div>

                <div id="savedJobsContainer" class="grid grid-cols-1 lg:grid-cols-2 gap-6"></div>

                <div id="emptyState" class="hidden text-center py-20">
                    <p class="text-lg text-gray-600 dark:text-gray-400">You haven't saved any jobs yet.</p>
                    <a href="<?= BASE_URL ?>" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Browse Jobs
                    </a>
                </div>
            </div>
            
            <!-- Sidebar with Latest Updates -->
            <div class="md:col-span-1">
                <?php if (!empty($latestUpdates)): ?>
                <div class="p-4 border rounded-xl bg-white dark:bg-gray-800 shadow sticky top-24">
                    <h3 class="text-sm font-semibold mb-3 dark:text-white">Latest Updates</h3>
                    <ul class="space-y-3">
                        <?php foreach ($latestUpdates as $lu): ?>
                        <li class="flex items-start gap-2">
                            <span class="inline-block mt-0.5 w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            <a href="<?= BASE_URL ?>job?slug=<?= e($lu['job_title_slug']) ?>" class="text-sm hover:underline dark:text-gray-300">
                                <?= e($lu['job_title']) ?>
                                <span class="block text-xs text-gray-500 mt-0.5"><?= date('M d', strtotime($lu['posted_date'])) ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Quick Links -->
                <div class="p-4 border rounded-xl bg-white dark:bg-gray-800 shadow mt-4">
                    <h3 class="text-sm font-semibold mb-3 dark:text-white">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a class="hover:underline dark:text-gray-300" href="<?= BASE_URL ?>mock_tests">Mock Tests</a></li>
                        <li><a class="hover:underline dark:text-gray-300" href="<?= BASE_URL ?>notifications">Push Alerts</a></li>
                        <li><a class="hover:underline dark:text-gray-300" href="<?= BASE_URL ?>contact">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .job-thumbnail {
        aspect-ratio: 16/9; /* keeps all thumbnails same ratio */
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .job-thumbnail:hover {
        transform: scale(1.03);
    }

    
    @media (max-width: 768px) {
        .max-w-6xl {
            width: 100%;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .grid {
            grid-template-columns: 1fr;
        }
        
        .gap-8 {
            gap: 1.5rem;
        }
    }
    </style>

    <script>
    async function fetchSavedJobs() {
        const container = document.getElementById('savedJobsContainer');
        const emptyState = document.getElementById('emptyState');
        container.innerHTML = `<div class="col-span-full flex justify-center py-10">
            <span class="text-gray-500 dark:text-gray-400">Loading saved jobs...</span>
        </div>`;

        let savedSlugs = JSON.parse(localStorage.getItem("saved_jobs") || "[]");

        if (savedSlugs.length === 0) {
            container.innerHTML = "";
            emptyState.classList.remove("hidden");
            return;
        } else {
            emptyState.classList.add("hidden");
        }

        try {
            const response = await fetch("/get-save-job.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ slugs: savedSlugs })
            });

            const jobs = await response.json();
            container.innerHTML = "";

            if (!jobs || jobs.length === 0) {
                container.innerHTML = `<p class="col-span-full text-center text-gray-500 dark:text-gray-400">No jobs found.</p>`;
                return;
            }

            jobs.forEach(job => {
                const card = document.createElement("div");
                // outer card must be flex + h-full
                card.className = "bg-white dark:bg-gray-800 shadow-lg rounded-xl border hover:shadow-xl transition overflow-hidden flex flex-col h-full";
                
                // description clean
                let description = job.description || '';
                description = description.replace(/#+\s*/g, '').replace(/\*\*(.*?)\*\*/g, '$1');

                // thumbnail
                const thumbnail = job.thumbnail 
                    ? `<img src="${job.thumbnail}" alt="${job.job_title}" class="job-thumbnail w-full object-cover">`
                    : '';

                // inner content
                card.innerHTML = `
                    ${thumbnail}
                    <div class="p-6 flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white truncate">${job.job_title}</h2>
                            <span class="px-3 py-1 text-xs bg-indigo-100 text-indigo-600 rounded-full whitespace-nowrap">Saved</span>
                        </div>
                        
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-2 font-medium">
                            ${job.company_name || "Unknown Company"}
                        </p>
                        
                        <p class="text-gray-500 dark:text-gray-400 text-xs flex items-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            ${job.location}
                        </p>
                        
                        <div class="line-clamp-3 text-gray-700 dark:text-gray-300 text-sm mb-4">
                            ${description.substring(0, 120)}${description.length > 120 ? '...' : ''}
                        </div>
                        
                        <!-- bottom area always at the end -->
                        <div class="mt-auto flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                ${new Date(job.posted_date).toLocaleDateString()}
                            </span>
                            <a href="<?= BASE_URL ?>job?slug=${job.job_title_slug}" 
                              class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white 
                                      rounded-lg hover:from-blue-700 hover:to-indigo-700 
                                      transition text-sm shadow-md whitespace-nowrap">
                                View Details
                            </a>
                        </div>
                    </div>
                `;

                container.appendChild(card);
            });

        } catch (error) {
            container.innerHTML = `<p class="col-span-full text-center text-red-500">Error loading saved jobs.</p>`;
            console.error("Fetch error:", error);
        }
    }

    // Load saved jobs when page loads
    document.addEventListener('DOMContentLoaded', fetchSavedJobs);
    </script>
</div>
