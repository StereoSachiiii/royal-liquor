<?php
require_once __DIR__ . "/../config/urls.php";
?>

<footer class="bg-black text-white py-20 px-8 md:px-16 mt-20 text-center">
    <div class="max-w-[1440px] mx-auto flex flex-col items-center gap-16">
        <!-- Brand Section -->
        <div class="flex flex-col items-center space-y-8 max-w-2xl">
            <h2 class="text-3xl font-heading tracking-[.4em] uppercase font-extrabold italic">Royal Liquor</h2>
            <p class="text-gray-400 text-sm leading-relaxed font-light tracking-wide italic">
                Purveyors of the world's finest spirits, rare vintages, and artisanal craft beverages since 1924. 
                Experience the pinnacle of curation.
            </p>
            <div class="flex gap-8 justify-center">
                <a href="#" class="text-gray-500 hover:text-white transition-colors">Twitter</a>
                <a href="#" class="text-gray-500 hover:text-white transition-colors">Instagram</a>
                <a href="#" class="text-gray-500 hover:text-white transition-colors">Facebook</a>
            </div>
        </div>

        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12 lg:gap-24 pt-16 border-t border-gray-900">
            <!-- Quick Links -->
            <div class="space-y-6">
                <h3 class="text-xs uppercase tracking-[.3em] font-extrabold text-white">The Collection</h3>
                <ul class="space-y-4 list-none p-0">
                    <li><a href="<?= PAGE_URLS['shop'] ?>" class="text-sm text-gray-400 hover:text-white transition-colors tracking-wide">Shop All</a></li>
                </ul>
            </div>

            <!-- Experience -->
            <div class="space-y-6">
                <h3 class="text-xs uppercase tracking-[.3em] font-extrabold text-white">Experience</h3>
                <ul class="space-y-4 list-none p-0">
                    <li><a href="<?= PAGE_URLS['contact'] ?>" class="text-sm text-gray-400 hover:text-white transition-colors tracking-wide">Private Concierge</a></li>
                    <li><a href="<?= PAGE_URLS['feedback'] ?>" class="text-sm text-gray-400 hover:text-white transition-colors tracking-wide">Customer Feedback</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="space-y-8 max-w-sm mx-auto sm:col-span-2 lg:col-span-1">
                <h3 class="text-xs uppercase tracking-[.3em] font-extrabold text-white">The Journal</h3>
                <p class="text-xs text-gray-500 uppercase tracking-widest leading-loose">
                    Subscribe for exclusive access to rare releases.
                </p>
                <form class="flex flex-col gap-4 border-b border-gray-800 pb-4">
                    <input type="email" placeholder="Email Address" class="bg-transparent border-none outline-none text-xs w-full uppercase tracking-widest text-center placeholder:text-gray-700">
                    <button type="submit" class="btn-premium !bg-white !text-black !h-12 w-full uppercase tracking-[.3em] font-bold hover:!bg-gray-100 transition-colors">Join Journal</button>
                </form>
            </div>
        </div>

        <!-- Copyright -->
        <div class="w-full mt-20 pt-10 border-t border-gray-900 flex flex-col items-center gap-8 text-[10px] uppercase tracking-[.2em] text-gray-600 font-bold">
            <span class="italic">&copy; <?= date('Y') ?> Royal Liquor Master Distillers. All rights reserved.</span>
            <div class="flex flex-wrap justify-center gap-12">
                <a href="<?= BASE_URL ?>privacy.php" class="hover:text-white">Privacy Policy</a>
                <a href="<?= BASE_URL ?>terms.php" class="hover:text-white">Terms of Release</a>
                <a href="<?= BASE_URL ?>returns.php" class="hover:text-white">Returns & Refunds</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= ASSET_URL ?>js/toast.js" type="module"></script>
</body>
</html>
