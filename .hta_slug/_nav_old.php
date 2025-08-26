<nav class="navbar navbar-expand-lg " style="background-color: #ffffff; box-shadow: 0 0 5px 0; color: #374151;">
    <div class="container">
        <a class="navbar-brand" href="/" style="color: #374151; font-weight: bold;">
            <!-- <img src="/assets/logo_dark.png" alt="Class Boxes Logo" style="width: 100px;" /> -->
             <p class="font-bold text-xl">JOB Notification</p>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="color: #374151; font-weight: bold;">
            <span class="navbar-toggler-icon d-flex flex-column justify-content-center align-items-center">
                <svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="Menu / Hamburger_MD"> <path id="Vector" d="M5 17H19M5 12H19M5 7H19" stroke="#374151" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g> </g></svg>
            </span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- <li class="nav-item"><a class="nav-link active" href="/" style="color: #374151;">Home</a></li> -->
                <?php
                    if(isset($_SESSION['isLogedin']) && $_SESSION['isLogedin'] === true){
                        if(isset($_SESSION['userType']) && $_SESSION['userType'] === 'admin'){
                            echo '
                            <li class="nav-item">
                                <a class="nav-link" href="/customers/new" style="color: #374151;">New Customer</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/customers/list" style="color: #374151;">Customer List</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/stat/" style="color: #374151;">Stat</a>
                            </li>

                            ';
                        } elseif(isset($_SESSION['userType']) && $_SESSION['userType'] === 'user')
                        echo '
                            <li class="nav-item">
                                <a class="nav-link" href="/my-account" style="color: #374151;">My Account</a>
                            </li>';
                    }
                ?>  
                <li class="nav-item">
                    <a class="nav-link" href="/contact-us" style="color: #374151;">Contact</a>
                </li>
                <?php
                    if(isset($_SESSION['isLogedin']) && $_SESSION['isLogedin'] === true){
                        echo '
                            <li class="nav-item">
                                <a class="nav-link" href="/profile" style="color: #374151;">Profile</a>
                            </li>
                        ';
                    }
                ?>
                <li class="nav-item">
                    <?php
                        if (isset($_SESSION['isLogedin']) && $_SESSION['isLogedin'] === true) {
                            echo '<a class="nav-link" href="/logout" style="color: #374151;">Logout</a>';
                        } else {
                            echo '<a class="nav-link" href="/login" style="color: #374151;">Login</a>';
                        }
                    ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main style="scroll-behavior: auto;">