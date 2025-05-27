# Generate Project Notes

A tool to combine various Laravel files into a single text file (project-notes.txt)
which can be uploaded to an AI assistant (e.g., Grok, Co-Pilot, or Perplexity.ai)
to provide an overview of the project without manually copying / pasting code each time.

Files are managed via project-files.json or a PHP array defined in the script’s loadFiles() function.

## Flags:

  --d        add a timestamp to the file or archive filename
  --size     display filesize after generation
  --zip      produce a zip archive of the text file
  --list     show a list of files which are included in the output
  --add      add a file (include full path)
  --remove   remove a file (include full path)
  --version  shows version information
  --help     displays the help file

## Usage:

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

## Examples:

  php .\generate-project-notes.php --d --size
    - Creates a timestamped project-notes-05-27-2025T12-03-00.txt and shows its size.
  php .\generate-project-notes.php --zip --d
    - Creates a timestamped text file and ZIP (e.g., project-notes-05-27-2025T12-03-00.zip).
  php .\generate-project-notes.php --add "app/Http/Controllers/HomeController.php"
    - Adds HomeController.php to the file list for future runs
  php .\generate-project-notes.php --remove "README.md"
    - Removes README.md from the file list for future runs
    
