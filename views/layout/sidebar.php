<?php
/**
 * Barra lateral de navegación
 * Ruta: views/layout/sidebar.php
 */
?>
<aside class="bg-gray-800 text-white w-64 min-h-screen fixed top-0 left-0 overflow-y-auto">
    <div class="p-4">
        <h2 class="text-lg font-semibold"><?php echo $_SESSION['user']['nombre']; ?></h2>
        <p class="text-sm text-gray-400"><?php echo $_SESSION['user']['rol']; ?></p>
    </div>
    <nav class="mt-4">
        <?php
        $menu = require_once __DIR__ . '/../../config/menu.php';
        $currentRole = $_SESSION['user']['rol'];
        
        foreach ($menu[$currentRole] as $item):
            if (isset($item['submenu'])): ?>
                <div class="menu-item">
                    <button class="w-full flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700">
                        <i class="<?php echo $item['icon']; ?> mr-3"></i>
                        <span><?php echo $item['text']; ?></span>
                    </button>
                    <div class="submenu hidden pl-8">
                        <?php foreach ($item['submenu'] as $subitem): ?>
                            <a href="<?php echo $subitem['url']; ?>" class="block py-2 text-sm text-gray-400 hover:text-white">
                                <?php echo $subitem['text']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $item['url']; ?>" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700">
                    <i class="<?php echo $item['icon']; ?> mr-3"></i>
                    <span><?php echo $item['text']; ?></span>
                </a>
            <?php endif;
        endforeach; ?>
    </nav>
</aside>