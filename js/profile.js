/**
 * js/profile.js - Profile View & Data Management Logic
 */

async function exportData() {
    const favs = await getUserData('favs');
    const watchlist = await getUserData('watchlist');
    const progress = await getUserData('watch_progress');

    const exportData = {
        favs,
        watchlist,
        progress,
        export_date: new Date().toISOString(),
        version: '1.0.0'
    };

    const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `tvq_tracker_backup_${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

async function importData() {
    const fileInput = document.getElementById('importFile');
    if (!fileInput || !fileInput.files.length) {
        alert('Please select a JSON file to import.');
        return;
    }

    const file = fileInput.files[0];
    const reader = new FileReader();

    reader.onload = async (e) => {
        try {
            const data = JSON.parse(e.target.result);
            if (data.favs) await saveUserData('favs', data.favs);
            if (data.watchlist) await saveUserData('watchlist', data.watchlist);
            if (data.progress) await saveUserData('watch_progress', data.progress);

            alert('Data imported successfully!');
            showView('profileView');
        } catch (err) {
            console.error('Import failed:', err);
            alert('Invalid backup file.');
        }
    };
    reader.readAsText(file);
}
