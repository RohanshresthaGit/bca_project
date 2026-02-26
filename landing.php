<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2E6B46 0%, #17402A 100%);
            min-height: 100vh;
        }

        .landing-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            padding: 20px 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .header-buttons {
            display: flex;
            gap: 15px;
        }

        /* Buttons */
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #5DA87A;
            color: white;
        }

        .btn-primary:hover {
            background: #2E6B46;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(93, 168, 122, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline:hover {
            background: white;
            color: #2E6B46;
        }

        .btn-secondary {
            background: white;
            color: #2E6B46;
        }

        .btn-secondary:hover {
            background: #f0f0f0;
        }

        .btn-large {
            padding: 15px 40px;
            font-size: 1.1rem;
        }

        /* Hero Section */
        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            padding: 60px 40px;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            width: 100%;
        }

        .hero-text {
            color: white;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 0.8s ease-out;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            animation: fadeInUp 1.2s ease-out;
        }

        .hero-image {
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 1.5s ease-out;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 968px) {
            .hero-content {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }

            .hero-title {
                font-size: 3rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .hero-buttons {
                justify-content: center;
            }

            .header-content {
                flex-direction: column;
                gap: 20px;
            }

            .logo {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 640px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-large {
                width: 100%;
            }

            .header-buttons {
                width: 100%;
                justify-content: center;
            }
            
            .hero {
                padding: 40px 20px;
            }
            
            .header-content {
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1 class="logo">Inventory Management System</h1>
                <div class="header-buttons">
                    <a href="index.php#login" class="btn btn-outline">Login</a>
                    <a href="index.php#signup" class="btn btn-primary">Sign Up</a>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h2 class="hero-title">Manage Your Inventory</h2>
                    <p class="hero-subtitle">Help to manage your inventory easily and efficiently</p>
                    <div class="hero-buttons">
                        <a href="index.php#signup" class="btn btn-large btn-primary">Get Started</a>
                        <a href="index.php#login" class="btn btn-large btn-secondary">Login</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="image/inventory.png" alt="Inventory Management" onerror="this.style.display='none'">
                </div>
            </div>
        </section>
    </div>
</body>
</html>