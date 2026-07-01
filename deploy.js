const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const archiver = require('archiver');

// --- AUTODETEKCJA I KONFIGURACJA ---
// Skrypt sam sprawdzi, który plik istnieje w katalogu
const PHP_FILE = fs.existsSync('simple-high-contrast.php')
    ? 'simple-high-contrast.php'
    : 'simple-high-contrast-resizer.php';

const HTML_FILE = 'index.html';
const ZIP_OUTPUT = PHP_FILE.replace('.php', '.zip');

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

    // 1. Wykrywanie aktywnej gałęzi Git (main lub master)
    let branchName = 'main';
    try {
        branchName = runCmd('git rev-parse --abbrev-ref HEAD');
    } catch (e) {
        console.log('ℹ️ Nie można odczytać aktywnej gałęzi Git, domyślnie użyta zostanie: main');
    }
    console.log(`🌿 Wykryta gałąź Git: ${branchName}`);

    // 2. Odczytanie i inkrementacja wersji w package.json
    const packageJsonPath = path.join(__dirname, 'package.json');
    if (!fs.existsSync(packageJsonPath)) {
        console.error('❌ Brak pliku package.json! Upewnij się, że jesteś w odpowiednim katalogu.');
        process.exit(1);
    }

    const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));
    const oldVersion = packageJson.version;

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
        console.log(`📝 Zaktualizowano wersję w pliku: ${PHP_FILE}`);
    } else {
        console.error(`❌ Nie znaleziono pliku PHP wtyczki (${PHP_FILE})!`);
        process.exit(1);
    }

    // 4. Aktualizacja wersji w index.html (tylko jeśli plik istnieje)
    const hasHtml = fs.existsSync(HTML_FILE);
    if (hasHtml) {
        let htmlContent = fs.readFileSync(HTML_FILE, 'utf8');
        htmlContent = htmlContent.replace(
            /<div class="badge">Wersja [\d\.]+[^<]*<\/div>/,
            `<div class="badge">Wersja ${newVersion} — Przycisk Resetu</div>`
        );
        fs.writeFileSync(HTML_FILE, htmlContent);
        console.log(`📝 Zaktualizowano wersję w ${HTML_FILE}`);
    } else {
        console.log(`ℹ️ Brak pliku ${HTML_FILE} – pomijanie aktualizacji wersji strony demo.`);
    }

    // 5. Budowanie archiwum ZIP
    console.log(`📦 Pakowanie wtyczki ${PHP_FILE} do formatu ZIP...`);
    await new Promise((resolve, reject) => {
        const output = fs.createWriteStream(path.join(__dirname, ZIP_OUTPUT));
        const archive = archiver('zip', { zlib: { level: 9 } });

        output.on('close', () => {
            console.log(`✅ Utworzono archiwum ZIP (${archive.pointer()} bajtów)`);
            resolve();
        });

        archive.on('error', (err) => reject(err));
        archive.pipe(output);

        archive.file(PHP_FILE, { name: PHP_FILE });
        archive.finalize();
    });

    // 6. Przygotowanie plików do zatwierdzenia w Git
    console.log('💾 Zatwierdzanie zmian w Git...');
    const filesToAdd = ['package.json', PHP_FILE];
    if (hasHtml) {
        filesToAdd.push(HTML_FILE);
    }

    runCmd(`git add ${filesToAdd.join(' ')}`);

    // Tworzenie commitu i tagu
    try {
        runCmd(`git commit -m "Zwiększono wersję do v${newVersion}"`);
        runCmd(`git tag -a v${newVersion} -m "Wersja v${newVersion}"`);
    } catch (e) {
        console.log('ℹ️ Brak zmian do zatwierdzenia w commitcie (lub commit już istnieje).');
    }

    // Push do GitHuba
    console.log(`📤 Wysyłanie zmian na gałąź ${branchName}...`);
    runCmd(`git push origin ${branchName} --follow-tags`);

    // 7. Publikacja na GitHub Pages (tylko jeśli jest index.html)
    if (hasHtml) {
        console.log('🚀 Publikowanie index.html na GitHub Pages...');
        runCmd('npx gh-pages -d . -f index.html');
        console.log('✅ Strona demonstracyjna została zaktualizowana!');
    }

    // 8. Tworzenie oficjalnego GitHub Release przy użyciu GitHub CLI (gh)
    console.log('🔔 Tworzenie wydania (Release) na GitHubie...');
    try {
        runCmd(`gh release create v${newVersion} ${ZIP_OUTPUT} --title "Wersja v${newVersion}" --notes "Automatyczne wydanie wersji v${newVersion}"`);
        console.log('🎉 GitHub Release został pomyślnie utworzony!');
    } catch (e) {
        console.warn('⚠️ Nie udało się automatycznie utworzyć Release na GitHubie. Upewnij się, że masz zainstalowane i zalogowane narzędzie "gh" CLI (uruchom "gh auth login").');
    }

    // Usunięcie lokalnego ZIP po udanym wdrożeniu
    if (fs.existsSync(ZIP_OUTPUT)) {
        fs.unlinkSync(ZIP_OUTPUT);
    }

    console.log(`🏁 Proces zakończony sukcesem! Aktualna wersja: ${newVersion}`);
}

main();