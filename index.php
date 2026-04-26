<?php
include("header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>KASSAH Vitals | Advanced Healthcare</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { 
                        brand: { 50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 500: '#14b8a6', 600: '#0d9488', 900: '#134e4a' },
                        accent: { 500: '#3b82f6', 600: '#2563eb' },
                        highlight: { 500: '#8b5cf6', 600: '#7c3aed' }
                    },
                    animation: { 
                        'blob': 'blob 7s infinite',
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' }
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>

<body class="w-full font-sans text-slate-300 antialiased relative bg-slate-950 overflow-x-hidden">

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[10%] w-[500px] h-[500px] bg-brand-500/30 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
        <div class="absolute top-[20%] right-[5%] w-[400px] h-[400px] bg-accent-500/30 rounded-full mix-blend-multiply filter blur-3xl animate-blob" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-[-10%] left-[40%] w-[600px] h-[600px] bg-highlight-500/30 rounded-full mix-blend-multiply filter blur-3xl animate-blob" style="animation-delay: 4s;"></div>
    </div>

    <nav class="fixed w-full z-50 bg-slate-950/80 backdrop-blur-lg border-b border-slate-800/50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3 cursor-pointer" onclick="window.scrollTo(0,0)">
                <div class="bg-gradient-to-br from-brand-500 to-blue-600 text-white p-2.5 rounded-xl shadow-lg shadow-brand-500/30">
                    <i class="fa-solid fa-hospital text-xl"></i>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white">KASSAH <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-500 to-accent-500">Vitals</span></h1>
            </div>
            <div class="hidden md:flex items-center gap-8">
                <a href="#home" class="text-brand-600 font-bold hover:text-brand-800 transition">Home</a>
                <a href="services.html" class="text-slate-600 font-bold hover:text-brand-600 transition">About Us</a>
                <a href="contact.html" class="text-slate-600 font-bold hover:text-brand-600 transition">Contact</a>
                <a href="#portals" class="bg-white text-slate-900 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-200 transition shadow-md">Access Portals</a>
            </div>
        </div>
    </nav>

    <section id="home" class="pt-40 pb-20 px-6 min-h-[90vh] flex flex-col justify-center items-center text-center max-w-5xl mx-auto animate-fade-in-up">
        <div class="inline-block bg-slate-800/60 border border-slate-700 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-bold text-brand-400 mb-6 shadow-sm">
            <i class="fa-solid fa-star text-yellow-400 mr-1"></i> Top Rated Hospital in the Region
        </div>
        <h2 class="text-5xl md:text-7xl font-black text-white mb-6 tracking-tight leading-tight">
            Advanced Healthcare,<br/>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-500 via-accent-500 to-brand-500 bg-[length:200%_auto] animate-pulse">Compassionate Care.</span>
        </h2>
        <p class="text-lg md:text-xl text-slate-400 font-medium mb-10 max-w-3xl leading-relaxed">
            Experience world-class medical treatment with cutting-edge technology and a team of dedicated specialists. Your health is our ultimate priority.
        </p>
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="register.php" class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 px-8 rounded-2xl transition duration-300 shadow-lg shadow-brand-500/30 flex items-center justify-center text-lg">
                Register as Patient <i class="fa-solid fa-arrow-right ml-2"></i>
            </a>
            <a href="#portals" class="bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 font-bold py-4 px-8 rounded-2xl transition duration-300 shadow-sm flex items-center justify-center text-lg">
                Login to Portal <i class="fa-solid fa-lock ml-2"></i>
            </a>
        </div>
    </section>

    <section class="py-12 border-y border-slate-800/50 bg-slate-900/40 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <h4 class="text-4xl font-black text-white mb-1">50+</h4>
                <p class="text-slate-400 font-bold uppercase tracking-wider text-sm">Specialist Doctors</p>
            </div>
            <div>
                <h4 class="text-4xl font-black text-brand-500 mb-1">24/7</h4>
                <p class="text-slate-400 font-bold uppercase tracking-wider text-sm">Emergency Care</p>
            </div>
            <div>
                <h4 class="text-4xl font-black text-white mb-1">10k+</h4>
                <p class="text-slate-400 font-bold uppercase tracking-wider text-sm">Happy Patients</p>
            </div>
            <div>
                <h4 class="text-4xl font-black text-accent-500 mb-1">100%</h4>
                <p class="text-slate-400 font-bold uppercase tracking-wider text-sm">Digital Records</p>
            </div>
        </div>
    </section>

    <section id="services" class="py-24 px-6 max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">Our Premium Services</h2>
            <p class="text-slate-500 font-medium max-w-2xl mx-auto">We provide a wide range of medical services utilizing the latest technology and top-tier medical professionals.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-slate-900/60 p-8 rounded-3xl shadow-sm border border-slate-800 hover:shadow-2xl hover:shadow-brand-500/10 transition duration-300 backdrop-blur-sm">
                <div class="w-14 h-14 bg-red-500/20 text-red-400 rounded-2xl flex items-center justify-center text-2xl mb-6"><i class="fa-solid fa-truck-medical"></i></div>
                <h3 class="text-xl font-bold text-white mb-3">Emergency Care</h3>
                <p class="text-slate-400 font-medium leading-relaxed">Round-the-clock emergency services with rapid response teams and state-of-the-art trauma centers ready to handle any critical situation.</p>
            </div>
            <div class="bg-slate-900/60 p-8 rounded-3xl shadow-sm border border-slate-800 hover:shadow-2xl hover:shadow-brand-500/10 transition duration-300 backdrop-blur-sm">
                <div class="w-14 h-14 bg-blue-500/20 text-blue-400 rounded-2xl flex items-center justify-center text-2xl mb-6"><i class="fa-solid fa-microscope"></i></div>
                <h3 class="text-xl font-bold text-white mb-3">Advanced Diagnostics</h3>
                <p class="text-slate-400 font-medium leading-relaxed">Comprehensive laboratory and imaging services ensuring precise, swift, and accurate diagnoses for effective treatment planning.</p>
            </div>
            <div class="bg-slate-900/60 p-8 rounded-3xl shadow-sm border border-slate-800 hover:shadow-2xl hover:shadow-brand-500/10 transition duration-300 backdrop-blur-sm">
                <div class="w-14 h-14 bg-brand-500/20 text-brand-400 rounded-2xl flex items-center justify-center text-2xl mb-6"><i class="fa-solid fa-heart-pulse"></i></div>
                <h3 class="text-xl font-bold text-white mb-3">Specialized Surgery</h3>
                <p class="text-slate-400 font-medium leading-relaxed">Expert surgical teams operating in advanced theaters, specializing in neurology, cardiology, orthopedics, and minimally invasive procedures.</p>
            </div>
        </div>
    </section>

    <section id="portals" class="py-24 px-6 bg-slate-950 text-white relative overflow-hidden border-t border-slate-800">
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-brand-500/30 rounded-full blur-3xl mix-blend-screen"></div>
        <div class="absolute bottom-[-20%] left-[-10%] w-[600px] h-[600px] bg-accent-500/30 rounded-full blur-3xl mix-blend-screen"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-4">Secure System Portals</h2>
                <p class="text-slate-400 font-medium max-w-2xl mx-auto">Select your designated access node below to securely log into the KASSAH Vitals management system.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 w-full">

                <div class="bg-white/10 backdrop-blur-xl p-8 rounded-3xl border border-white/20 flex flex-col hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-16 h-16 bg-blue-500/20 text-blue-400 rounded-2xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-bed-pulse"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Patient Portal</h3>
                    <p class="text-slate-300 text-sm font-medium mb-8 flex-1">Access your medical records, view live vitals, and book appointments with our specialists.</p>
                    
                    <div class="space-y-3 mt-auto">
                        <a href="index1.php" class="flex justify-center items-center w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-3.5 rounded-xl transition duration-300 shadow-md">
                            Sign In <i class="fa-solid fa-arrow-right-to-bracket ml-2"></i>
                        </a>
                        <a href="register.php" class="flex justify-center items-center w-full bg-white/5 hover:bg-white/10 text-white border border-white/20 font-bold py-3.5 rounded-xl transition duration-300">
                            Create Account
                        </a>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-xl p-8 rounded-3xl border border-white/20 flex flex-col hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-16 h-16 bg-brand-500/20 text-brand-400 rounded-2xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Doctor Access</h3>
                    <p class="text-slate-300 text-sm font-medium mb-6">Manage patient appointments, update medical logs, and prescribe treatments.</p>
                    
                    <form method="post" action="func1.php" class="mt-auto space-y-4">
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-4 top-3.5 text-slate-400"></i>
                            <input type="email" name="email3" placeholder="Doctor Email ID" required autocomplete="off"
                                class="w-full pl-11 pr-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 outline-none transition text-sm">
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400"></i>
                            <input type="password" name="password3" placeholder="Password" required autocomplete="new-password"
                                class="w-full pl-11 pr-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-brand-500 outline-none transition text-sm">
                        </div>
                        <button type="submit" name="docsub1" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition duration-300 shadow-md flex justify-center items-center">
                            Authenticate <i class="fa-solid fa-shield-halved ml-2"></i>
                        </button>
                    </form>
                </div>

                <div class="bg-white/10 backdrop-blur-xl p-8 rounded-3xl border border-white/20 flex flex-col hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-16 h-16 bg-purple-500/20 text-purple-400 rounded-2xl flex items-center justify-center text-2xl mb-6">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Core Command</h3>
                    <p class="text-slate-300 text-sm font-medium mb-6">System configuration, personnel management, and global oversight.</p>
                    
                    <form method="post" action="func3.php" class="mt-auto space-y-4">
                        <div class="relative">
                            <i class="fa-solid fa-terminal absolute left-4 top-3.5 text-slate-400"></i>
                            <input type="text" name="username1" placeholder="Admin Username" required autocomplete="off"
                                class="w-full pl-11 pr-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 outline-none transition text-sm">
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-key absolute left-4 top-3.5 text-slate-400"></i>
                            <input type="password" name="password2" placeholder="Admin Passkey" required autocomplete="new-password"
                                class="w-full pl-11 pr-4 py-3 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 outline-none transition text-sm">
                        </div>
                        <button type="submit" name="adsub" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3.5 rounded-xl transition duration-300 shadow-md flex justify-center items-center">
                            Execute Login <i class="fa-solid fa-bolt ml-2"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </section>

    <footer class="bg-slate-950 text-slate-400 py-12 px-6 border-t border-slate-800">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div>
                <h3 class="text-white font-bold text-lg mb-4">KASSAH Vitals</h3>
                <p class="text-sm leading-relaxed">Providing world-class medical facilities and top-tier healthcare professionals to ensure your well-being.</p>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#home" class="hover:text-brand-400 transition">Home</a></li>
                    <li><a href="services.html" class="hover:text-brand-400 transition">About Us</a></li>
                    <li><a href="#portals" class="hover:text-brand-400 transition">Patient Portal</a></li>
                    <li><a href="contact.html" class="hover:text-brand-400 transition">Contact Support</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg mb-4">Contact Info</h3>
                <ul class="space-y-2 text-sm">
                    <li><i class="fa-solid fa-location-dot mr-2 w-4"></i> Kolkata, West Bengal, India</li>
                    <li><i class="fa-solid fa-phone mr-2 w-4"></i> +91 70031 23456</li>
                    <li><i class="fa-solid fa-envelope mr-2 w-4"></i> support@kassah.org</li>
                </ul>
            </div>
        </div>
        <div class="text-center pt-8 border-t border-slate-800 text-sm">
            &copy; <?= date('Y') ?> KASSAH Vitals. All rights reserved.
        </div>
    </footer>

</body>
</html>