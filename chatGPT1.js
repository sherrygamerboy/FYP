const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();

app.get('/download', (req, res) => {
    const filename = req.query.filename;

    if (!filename) {
        return res.status(400).send('Filename is required');
    }

    // Define base directory
    const baseDir = path.join(__dirname, 'user_data');

    // Resolve full path safely
    const filePath = path.join(baseDir, filename);
    const resolvedPath = path.resolve(filePath);

    // Ensure the resolved path is inside baseDir
    if (!resolvedPath.startsWith(baseDir)) {
        return res.status(403).send('Access denied');
    }

    // Check if file exists and is a file
    fs.stat(resolvedPath, (err, stats) => {
        if (err || !stats.isFile()) {
            return res.status(404).send('File not found');
        }

        // Send file
        res.sendFile(resolvedPath);
    });
});

app.listen(3000, () => {
    console.log('Server running on port 3000');
});