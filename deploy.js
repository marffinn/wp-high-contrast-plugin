const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const archiver = require('archiver');

// --- CONFIGURATION ---
const PHP_FILE = 'simple-high-contrast-resizer.php';
const HTML_FILE = 'index.html';
const ZIP_OUTPUT = 'simple-high-contrast-resizer.zip';

function runCmd(cmd) {
    try {
        return execSync(cmd, { stdio: 'pipe' }).toString().trim();
    } catch (error) {
        console.error(`Błąd podczas wykonywania: ${cmd}`);
        console.error(error.message);
        process.exit(1);
    }
}

async function main() {
    console.log('🔄 Rozpoczynanie procedury deploy...');

    // 1. Sprawdzenie stanu Git
    const status = runCmd('git status --porcelain');
    if (status) {
        console.log('⚠️ Masz niezatwierdzone zmiany w gicie. Zatwierdź je przed wdrożeniem.');
        process.exit(1);
    }

    // 2. Odczytanie i inkrementacja wersji w package.json
    const packageJsonPath = path.join(__dirname, 'package.json');
    const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
    const oldVersion = packageJson.version;

    // Prosta inkrementacja PATCH (np. 1.3.0 -> 1.3.1)
    const parts = oldVersion.split('.').map(Number);
    parts[2] += 1;
    const newVersion = parts.join('.');

    packageJson.version = newVersion;
    fs.writeFileSync(packageJsonPath, JSON.stringify(packageJson, null, 2) + '\n');
    console.log(`📈 Podbito wersję w package.json: ${oldVersion} -> ${newVersion}`);

    // 3. Aktualizacja nagłówka Version w pliku PHP
    if (fs.existsSync(PHP_FILE)) {
        let phpContent = fs.readFileSync(PHP_FILE, 'utf8');
        phpContent = phpContent.replace(
            /Version:\s+[\d\.]+/,
            `Version:           ${newVersion}`
        );
        fs.writeFileSync(PHP_FILE, phpContent);
        console.log(`📝 Zaktualizowano wersję w ${PHP_FILE}`);
    } else {
        console.warn(`⚠️ Brak pliku ${PHP_FILE}!`);
    }

    // 4. Aktualizacja wersji w index.html (Badge)
    if (fs.existsSync(HTML_FILE)) {
        let htmlContent = fs.readFileSync(HTML_FILE, 'utf8');
        htmlContent = htmlContent.replace(
            /<div class="badge">Wersja [\d\.]+[^<]*<\/div>/,
            `<div class="badge">Wersja ${newVersion} — Przycisk Resetu</div>`
        );
        fs.writeFileSync(HTML_FILE, htmlContent);
        console.log(`📝 Zaktualizowano wersję w ${HTML_FILE}`);
    }

    // 5. Budowanie archiwum ZIP w pamięci / pliku
    console.log('📦 Pakowanie wtyczki do formatu ZIP...');
    await new Promise((resolve, reject) => {
        const output = fs.createWriteStream(path.join(__dirname, ZIP_OUTPUT));
        const archive = archiver('zip', { zlib: { level: 9 } });

        output.on('close', () => {
            console.log(`✅ Utworzono archiwum ZIP (${archive.pointer()} bajtów)`);
            resolve();
        });

        archive.on('error', (err) => reject(err));
        archive.pipe(output);

        // Dodajemy tylko plik PHP (możesz dopisać kolejne pliki, np. readme.txt)
        archive.file(PHP_FILE, { name: PHP_FILE });
        archive.finalize();
    });

    // 6. Commit i Tagowanie Git
    console.log('💾 Zatwierdzanie zmian w Git...');
    runCmd('git add package.json ' + PHP_FILE + ' ' + HTML_FILE);
    runCmd(`git commit -m "Zwiększono wersję do v${newVersion}"`);
    runCmd(`git tag -a v${newVersion} -m "Wersja v${newVersion}"`);
    runCmd('git push origin main --follow-tags');

    // 7. Publikacja na GitHub Pages
    console.log('🚀 Publikowanie index.html na GitHub Pages...');
    runCmd('npx gh-pages -d . -f index.html');
    console.log('✅ Strona demonstracyjna została zaktualizowana!');

    // 8. Tworzenie oficjalnego GitHub Release przy użyciu GitHub CLI (gh)
    console.log('🔔 Tworzenie wydania (Release) na GitHubie...');
    try {
        runCmd(`gh release create v${newVersion} ${ZIP_OUTPUT} --title "Wersja v${newVersion}" --notes "Automatyczne wydanie wersji v${newVersion}"`);
        console.log('🎉 GitHub Release został pomyślnie utworzony i spakowany plik .ZIP został załączony!');
    } catch (e) {
        console.warn('⚠️ Nie udało się automatycznie utworzyć Release na GitHubie. Upewnij się, że masz zainstalowane i zalogowane narzędzie "gh" CLI (uruchom "gh auth login").');
    }

    // Usunięcie lokalnego ZIP po udanym wdrożeniu (opcjonalne)
    if (fs.existsSync(ZIP_OUTPUT)) {
        fs.unlinkSync(ZIP_OUTPUT);
    }

    console.log(`🏁 Proces zakończony sukcesem! Aktualna wersja: ${newVersion}`);
}

main();