const express = require('express');
const fs = require('fs');
const path = require('path');

const app = express();

app.get('/download', (req, res) => {
    const filename = req.query.filename;

    if (!filename) {
        return res.status(400).send('Missing filename');
    }

    // Resolve the file path safely inside /user_data/
    const baseDir = path.join(__dirname, 'user_data');
    const filePath = path.join(baseDir, filename);

    // Prevent path traversal (e.g., ../../etc/passwd)
    if (!filePath.startsWith(baseDir)) {
        return res.status(400).send('Invalid filename');
    }

    // Check if file exists
    fs.access(filePath, fs.constants.F_OK, err => {
        if (err) {
            return res.status(404).send('File not found');
        }

        // Send the file
        res.sendFile(filePath);
    });
});

app.listen(3000, () => {
    console.log('Server running on port 3000');
});
