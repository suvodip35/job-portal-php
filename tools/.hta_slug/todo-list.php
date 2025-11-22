<?php
$metaTitle = "Advanced Online To-Do List – Edit, Filter, Drag & Drop | Free Task Manager";
$pageDescription = "Use our free advanced online To-Do List tool to add, edit, complete, filter, reorder, and manage daily tasks. Auto-backup, drag & drop, and JSON export included.";
$keywords = "To-Do List, Online To-Do Tool, Task Manager, Productivity App, Task Organizer, Edit Tasks, Reorder Tasks, Filter Tasks, JSON Export Tasks, Free To-Do List";
$ogImage = "https://fromcampus.com/assets/assets/tools-image/todo-list.jpg";
$canonicalUrl = "https://fromcampus.com/tools/to-do-list";

$schema = [
    "@context" => "https://schema.org",
    "@type" => "SoftwareApplication",
    "name" => "Online To-Do List Tool",
    "applicationCategory" => "ProductivityApplication",
    "operatingSystem" => "All",
    "description" => $pageDescription,
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
<div class="flex items-center justify-center">

    <div class="w-full my-8 max-w-2xl p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transition-all duration-300">

        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800 dark:text-white">
            To-Do List
        </h2>

        <!-- Add Task -->
        <div class="flex mb-4 gap-2">
            <input 
                type="text"
                id="taskInput"
                placeholder="Add a new task"
                class="flex-grow p-3 border-2 rounded-lg dark:bg-gray-700 dark:text-white dark:border-gray-600"/>

            <button onclick="addTask()" class="bg-blue-500 text-white px-6 rounded-lg hover:bg-blue-600">
                Add
            </button>
        </div>

        <!-- Filters -->
        <div class="flex justify-between mb-4">
            <button onclick="filterTasks('all')" class="filter-btn">All</button>
            <button onclick="filterTasks('completed')" class="filter-btn">Completed</button>
            <button onclick="filterTasks('pending')" class="filter-btn">Pending</button>

            <button onclick="exportJSON()" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                Export JSON
            </button>
        </div>

        <ul id="taskList" class="list-none space-y-4"></ul>

    </div>
</div>
<div class="mt-8 p-6 bg-gray-100 dark:bg-gray-700 rounded-lg shadow">
    <h3 class="text-2xl font-bold mb-3 text-gray-900 dark:text-white">
        To-Do List – Usage & Purpose
    </h3>
    <p class="text-gray-700 dark:text-gray-300 mb-2">
        The Online To-Do List tool helps you organize and manage your daily tasks efficiently. 
        It is designed for students, professionals, and anyone who wants to stay productive 
        by keeping track of tasks, deadlines, and priorities.
    </p>
    <ul class="list-disc ml-6 text-gray-700 dark:text-gray-300 space-y-1">
        <li>Add, edit, and delete tasks easily with a simple interface.</li>
        <li>Mark tasks as completed to keep track of your progress.</li>
        <li>Filter tasks by All, Completed, or Pending for better task management.</li>
        <li>Drag and drop tasks to reorder based on priority.</li>
        <li>Export tasks as JSON and save a backup in your browser for future reference.</li>
        <li>Works entirely in your browser — your data never leaves your device.</li>
    </ul>

    <h3 class="text-xl font-semibold mt-5 text-gray-900 dark:text-white">Why Use This Tool?</h3>
    <p class="text-gray-700 dark:text-gray-300">
        Staying organized is essential for productivity. Whether you are preparing for exams, 
        managing work assignments, or planning personal tasks, this To-Do List tool helps you 
        track and complete your tasks efficiently. Its features like auto-save, filtering, 
        and drag-and-drop make task management effortless.
    </p>
</div>

<script>
document.addEventListener("DOMContentLoaded", loadTasks);

function loadTasks() {
    const taskList = document.getElementById("taskList");
    const tasks = JSON.parse(localStorage.getItem("tasks")) || [];

    tasks.forEach((task) => {
        const item = createTaskItem(task);
        taskList.appendChild(item);
    });
}

function addTask() {
    const input = document.getElementById("taskInput");
    const text = input.value.trim();
    if (!text) return;

    const newTask = {
        id: Date.now(),
        text,
        date: new Date().toLocaleString(),
        completed: false
    };

    saveTask(newTask);
    document.getElementById("taskList").appendChild(createTaskItem(newTask));
    input.value = "";
}

function createTaskItem(task) {
    const li = document.createElement("li");
    li.setAttribute("data-id", task.id);
    li.setAttribute("draggable", true);

    li.className = `
        flex justify-between items-center p-4 
        bg-gray-50 dark:bg-gray-700 rounded-lg shadow-md
    `;

    /* --- Task Text Box --- */
    const left = document.createElement("div");

    const span = document.createElement("span");
    span.textContent = task.text;
    span.className = "text-gray-800 dark:text-white font-medium cursor-pointer";

    if (task.completed) span.classList.add("line-through", "text-green-500");

    span.onclick = () => editTask(task.id);

    const date = document.createElement("div");
    date.textContent = task.date;
    date.className = "text-xs text-gray-500";

    left.appendChild(span);
    left.appendChild(date);

    /* --- Action Buttons --- */
    const actions = document.createElement("div");
    actions.className = "flex gap-3";

    // Complete Button
    const completeBtn = document.createElement("button");
    completeBtn.innerHTML = "✔️";
    completeBtn.onclick = () => toggleComplete(task.id, span);
    actions.appendChild(completeBtn);

    // Delete Button
    const deleteBtn = document.createElement("button");
    deleteBtn.innerHTML = "❌";
    deleteBtn.onclick = () => deleteTask(task.id, li);
    actions.appendChild(deleteBtn);

    li.appendChild(left);
    li.appendChild(actions);

    /* Drag Events */
    li.addEventListener("dragstart", dragStart);
    li.addEventListener("dragover", dragOver);
    li.addEventListener("drop", dropItem);

    return li;
}

/* --- Save Task --- */
function saveTask(task) {
    const tasks = JSON.parse(localStorage.getItem("tasks")) || [];
    tasks.push(task);
    localStorage.setItem("tasks", JSON.stringify(tasks));
}

/* --- Edit Task --- */
function editTask(id) {
    const tasks = JSON.parse(localStorage.getItem("tasks"));
    const task = tasks.find(t => t.id === id);

    const newText = prompt("Edit Task:", task.text);
    if (!newText) return;

    task.text = newText;
    localStorage.setItem("tasks", JSON.stringify(tasks));

    location.reload();
}

/* --- Mark Complete --- */
function toggleComplete(id, span) {
    const tasks = JSON.parse(localStorage.getItem("tasks"));
    const task = tasks.find(t => t.id === id);

    task.completed = !task.completed;
    localStorage.setItem("tasks", JSON.stringify(tasks));

    span.classList.toggle("line-through");
    span.classList.toggle("text-green-500");
}

/* --- Delete Task --- */
function deleteTask(id, li) {
    li.remove();
    let tasks = JSON.parse(localStorage.getItem("tasks"));
    tasks = tasks.filter(t => t.id !== id);
    localStorage.setItem("tasks", JSON.stringify(tasks));
}

/* --- Filters --- */
function filterTasks(type) {
    const list = document.getElementById("taskList");
    const tasks = JSON.parse(localStorage.getItem("tasks")) || [];

    list.innerHTML = "";

    let filtered = tasks;
    if (type === "completed") filtered = tasks.filter(t => t.completed);
    if (type === "pending") filtered = tasks.filter(t => !t.completed);

    filtered.forEach(t => list.appendChild(createTaskItem(t)));
}

/* --- Export JSON --- */
function exportJSON() {
    const tasks = localStorage.getItem("tasks");
    const blob = new Blob([tasks], { type: "application/json" });
    const url = URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = "todo-list.json";
    a.click();
}

/* --- Drag & Drop Functions --- */
let dragged;

function dragStart(e) {
    dragged = this;
}

function dragOver(e) {
    e.preventDefault();
}

function dropItem(e) {
    e.preventDefault();
    if (dragged !== this) {
        this.before(dragged);
        saveOrder();
    }
}

function saveOrder() {
    const items = document.querySelectorAll("#taskList li");
    const reordered = [];

    items.forEach(item => {
        const id = Number(item.getAttribute("data-id"));
        const tasks = JSON.parse(localStorage.getItem("tasks"));
        const match = tasks.find(t => t.id === id);
        reordered.push(match);
    });

    localStorage.setItem("tasks", JSON.stringify(reordered));
}
</script>
<style>
.filter-btn {
    background: #ddd;
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 600;
    transition: 0.2s;
}
.filter-btn:hover {
    background: #bbb;
}
</style>
