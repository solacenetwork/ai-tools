<?php
// ------------------------------------------------------------
// generate-project-notes.php
// ------------------------------------------------------------

$version = "1.0.0 beta"; 

$helpText = <<<HELP

            Generate Project Notes:

            A tool to combine various Laravel files into a single text file (project-notes.txt)
            which can be uploaded to an AI assistant (e.g., Grok, Co-Pilot, or Perplexity.ai)
            to provide an overview of the project without manually copying / pasting code each time.

            Files are managed via project-files.json or a PHP array defined in the script’s loadFiles() function.

            Flags:

            --d        date flag - adds a timestamp to the file or archive filename
            --size     display filesize after generation
            --zip      produce a zip archive of the text file
            --list     show a list of files which are included in the output
            --add      add a file (include full path)
            --remove   remove a file (include full path)
            --version  shows version information
            --help     displays the help file
            --clean    removes all .txt and .zip files from /tools directory
            --o        output project-notes.txt file to a new location (e.g., C:\Users\User\Desktop)

            Usage:

            php .\generate-project-notes.php --version
                - Displays version information

            php .\generate-project-notes.php --help
                - Displays the program help info  
                
            php .\generate-project-notes.php
                - Generates project-notes.txt with the current file list.
                - Creates project-files.json if it doesn't exist.

            php .\generate-project-notes.php --d
                - Generates a timestamped file (e.g., project-notes-05-27-2025T12-03-00.txt).

            php .\generate-project-notes.php --size
                - Shows the size of the output file in KB.

            php .\generate-project-notes.php --zip
                - Creates a ZIP archive (e.g., project-notes.zip) containing the text file.

            php .\generate-project-notes.php --list
                - Lists files in project-files.json (or defaults if no JSON file).

            php .\generate-project-notes.php --add "path/to/file"
                - Adds a file to project-files.json (e.g., --add "app/Models/User.php").

            php .\generate-project-notes.php --remove "path/to/file"
                - Removes a file from project-files.json (e.g., --remove "readme.md").

            php .\generate-project-notes.php --o "C:\Users\User\Desktop"
                - Output project-notes.txt file to a new location (e.g., C:\Users\User\Desktop).    

            Examples:

            php .\generate-project-notes.php --d --size
                - Creates a timestamped project-notes-05-27-2025T12-03-00.txt and shows its size.

            php .\generate-project-notes.php --zip --d
                - Creates a timestamped text file and ZIP (e.g., project-notes-05-27-2025T12-03-00.zip).

            php .\generate-project-notes.php --add "app/Http/Controllers/HomeController.php"
                - Adds HomeController.php to the file list for future runs

            php .\generate-project-notes.php --remove "README.md"
                - Removes README.md from the file list for future runs


    HELP;

// Define constants
define('CONFIG_FILE', __DIR__ . '/project-files.json'); // Store config in /tools
define('DEFAULT_OUTPUT', __DIR__ . '/project-notes.txt'); // Store output in /tools

// Get project root (one level up from /tools)
define('PROJECT_ROOT', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

// Initialize or load the list of files from JSON
function loadFiles() {
    if (file_exists(CONFIG_FILE)) {
        $json = file_get_contents(CONFIG_FILE);
        return json_decode($json, true) ?: [];
    }
    // Default files (relative to project root)
    return [
        '.env',
        'README.md',
        'composer.json',
        'vite.config.js',
        'tailwind.config.js',
        'routes\web.php',
        'app\Providers\AppServiceProvider.php',
        'app\Providers\FortifyServiceProvider.php',
        'app\Providers\RouteServiceProvider.php',
        'app\Http\Middleware\AdjustSessionLifetime.php',
        'app\Http\Responses\CustomLoginResponse.php',
        'app\Models\User.php',
        'resources\views\layouts\guest.blade.php',
        'resources\css\app.css',
        'resources\js\app.js',
        'app\Livewire\JoinForm.php',
        'resources\views\livewire\join-form.blade.php',
        'resources\views\livewire\steps\step1.blade.php',
        'resources\views\livewire\steps\step2.blade.php',
        'resources\views\livewire\steps\step3.blade.php',
        'resources\views\livewire\steps\step4.blade.php',
        'resources\views\livewire\steps\step5.blade.php',
        'resources\views\livewire\steps\step6.blade.php',
        'resources\views\livewire\steps\step7.blade.php',
        'resources\views\partials\single.blade.php',
        'resources\views\partials\family.blade.php',
        'resources\views\partials\form-data.blade.php',
        'resources\views\partials\success.blade.php',
        'config\app.php',
        'config\cache.php',
        'config\session.php',
        'config\database.php',
        'resources\views\auth\login.blade.php',
        'resources\views\auth\register.blade.php'
    ];
}

// Save the file list to JSON
function saveFiles($files) {
    file_put_contents(CONFIG_FILE, json_encode($files, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// Normalize file paths for Windows (convert to forward slashes for consistency)
function normalizePath($path) {
    return str_replace('\\', '/', $path);
}

// Resolve file path relative to project root
function resolvePath($file) {
    return normalizePath(PROJECT_ROOT . $file);
}

// Parse command-line options
$options = getopt('', ['zip', 'd', 'size', 'list', 'add:', 'remove:','help', 'version', 'clean','o:']);
$files = loadFiles();

// ----------- CHANGES START HERE -----------

// Set output directory (default: script directory)
$outputDir = __DIR__;
if (isset($options['o'])) {
    $outputDir = rtrim($options['o'], DIRECTORY_SEPARATOR);
    if (!is_dir($outputDir)) {
        echo "Error: Output directory does not exist: $outputDir\n";
        exit(1);
    }
}

// Set filename based on --d flag
$useDate = isset($options['d']);
if ($useDate) {
    $timestamp = date('m-d-Y\TH-i-s');
    $filename = "project-notes-$timestamp.txt";
} else {
    $filename = "project-notes.txt";
}

// Build full output path
$outputPath = $outputDir . DIRECTORY_SEPARATOR . $filename;

// ----------- CHANGES END HERE -----------

// Generate the output text file
function generateOutputFile($files, $useDate, $outputPath) {
    $output = '';
    foreach ($files as $file) {
        $fullPath = resolvePath($file);
        $displayPath = normalizePath($file);
        $output .= "--------------------------------------------------\n";
        $output .= "$displayPath\n";
        $output .= "--------------------------------------------------\n\n";
        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);
            $output .= "$content\n\n";
        } else {
            $output .= "[File not found]\n\n";
        }
    }
    file_put_contents($outputPath, $output);

    // Save the file list to project-files.json if it doesn't exist
    if (!file_exists(CONFIG_FILE)) {
        saveFiles($files);
    }
    return $outputPath;
}

// Create a ZIP archive of the output file
function createZip($outputFile) {
    $zipFile = str_replace('.txt', '.zip', $outputFile);
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $zip->addFile($outputFile, basename($outputFile));
        $zip->close();
        return $zipFile;
    }
    return false;
}

// Get file size in KB
function getFileSizeKB($file): float {
    if (file_exists($file)) {
        return round(filesize($file) / 1024, 2);
    }
    return 0.0;
}

// Handle --add
if (isset($options['add'])) {
    $newFile = normalizePath($options['add']);
    if (!in_array($newFile, $files)) {
        $files[] = $newFile;
        saveFiles($files);
        echo "Added $newFile to the file list.\n";
    } else {
        echo "$newFile is already in the file list.\n";
    }
    exit;
}

// Handle --help
if (isset($options['help'])) {
    showHelp($helpText);
    exit;
}

// Handle --version
if (isset($options['version'])) {
    echo "\nGenerate Project Notes version: " . $version . "\n";
    echo "created 5/27/25 by ken@kendawson.online\n\n";
    exit;
}

// Handle --clean
if (isset($options['clean'])) {
    $dir = __DIR__;
    $keep = [
        'generate-project-notes.php',
        'project-files.json'
    ];

    // Find all .txt and .zip files in the directory
    $filesToDelete = [];
    foreach (scandir($dir) as $file) {
        if (
            !in_array($file, $keep) &&
            (preg_match('/\.txt$/i', $file) || preg_match('/\.zip$/i', $file)) &&
            is_file($dir . DIRECTORY_SEPARATOR . $file)
        ) {
            $filesToDelete[] = $file;
        }
    }

    if (empty($filesToDelete)) {
        echo "No generated .txt or .zip files to remove.\n";
        exit;
    }

    echo "The following files will be deleted:\n";
    foreach ($filesToDelete as $file) {
        echo "  - $file\n";
    }
    echo "Are you sure you want to remove all these files? (y/n): ";
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    if(trim(strtolower($line)) != 'y'){
        echo "Aborted.\n";
        exit;
    }
    fclose($handle);

    foreach ($filesToDelete as $file) {
        unlink($dir . DIRECTORY_SEPARATOR . $file);
    }
    echo "Deleted " . count($filesToDelete) . " files.\n";
    exit;
}

// Handle --remove
if (isset($options['remove'])) {
    $removeFile = normalizePath($options['remove']);
    if (($key = array_search($removeFile, $files)) !== false) {
        unset($files[$key]);
        $files = array_values($files); // Reindex array
        saveFiles($files);
        echo "Removed $removeFile from the file list.\n";
    } else {
        echo "$removeFile is not in the file list.\n";
    }
    exit;
}

// Handle --list
if (isset($options['list'])) {
    if (empty($files)) {
        echo "No files in the list.\n";
    } else {
        echo "Files in the project list:\n";
        foreach ($files as $file) {
            echo "- $file\n";
        }
    }
    exit;
}

// Generate the output file
echo "Generating Project Notes...\n";
$outputFile = generateOutputFile($files, $useDate, $outputPath);

// Prepare summary
$summary = "Processed " . count($files) . " files in list.\n";
$summary .= "Project Notes: " . normalizePath($outputFile);
if (isset($options['size'])) {
    $sizeKB = getFileSizeKB($outputFile);
    $summary .= " ($sizeKB KB)";
}
$summary .= "\n";

// Handle --zip
if (isset($options['zip'])) {
    $zipFile = createZip($outputFile);
    if ($zipFile) {
        $zipSizeKB = getFileSizeKB($zipFile);
        $summary .= "Archive: " . normalizePath($zipFile) . " ($zipSizeKB KB)\n";
    } else {
        $summary .= "Failed to create ZIP archive.\n";
    }
}

// Add timestamp to summary
$summary .= "Last ran at: " . date('m-d-Y\TH-i-s') . "\n"; // Output summary
echo $summary;

// Functions

function showHelp($helpText) {
    echo $helpText;
    exit(0);
}