<?php
$dir = new RecursiveDirectoryIterator('C:\xamppp\htdocs\Hospital-Management-System');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/\.(php|html|md)$/');

foreach($files as $file) {
    $path = $file->getPathname();
    $content = file_get_contents($path);
    if($content === false) continue;
    $orig = $content;
    
    // Plural specific
    $content = str_ireplace('KASSAH Vitalss', 'KASSAH Vitals', $content);
    $content = str_ireplace('KASSAH Vitals', 'KASSAH Vitals', $content);
    $content = str_ireplace('KASSAH Vitals', 'KASSAH Vitals', $content);
    $content = str_ireplace('KASSAH Vitals Clinical', 'KASSAH Vitals', $content);
    $content = str_ireplace('KASSAH Vitals', 'KASSAH Vitals', $content);
    
    // Span replacements (like: KASSAH Vitals <span...>Health</span>)
    $content = preg_replace('/KASSAH Vitals(.*?<span[^>]*>)Health/i', 'KASSAH$1Vitals', $content);
    $content = preg_replace('/KASSAH Vitals(.*?<span[^>]*>)Clinical/i', 'KASSAH$1Vitals', $content);
    $content = preg_replace('/KASSAH Vitals(.*?<span[^>]*>)Admin/i', 'KASSAH$1Admin', $content);
    
    // Remaining generic
    $content = str_ireplace('KASSAH Vitals@KASSAH Vitals.org', 'support@kassah.org', $content);
    $content = str_ireplace('support@KASSAH Vitals.org', 'support@kassah.org', $content);
    $content = str_ireplace('@KASSAH Vitals.org', '@kassah.org', $content);
    $content = str_replace('KASSAH Vitals', 'KASSAH', $content);
    $content = str_replace('KASSAH Vitals', 'KASSAH', $content);
    $content = str_replace('KASSAH Vitals', 'kassah', $content);
    
    if($orig !== $content) {
        file_put_contents($path, $content);
        echo "Updated $path\n";
    }
}
echo "Done replacing.";
?>
