const express = require('express');
const path = require('path');
const fs = require('fs');
const app = express();

app.get('/download', (req, res) => {
    const fileName = req.query.filename;

    if (!fileName) {
        return res.status(400).send('Filename is required.');
    }

    // 1. Define the base directory
    const baseDir = path.join(__dirname, 'user_data');

    // 2. Sanitize the filename to prevent directory traversal
    // path.basename returns only the last portion of a path (e.g., 'image.png')
    const safeFileName = path.basename(fileName);

    // 3. Construct the absolute path
    const filePath = path.join(baseDir, safeFileName);

    // 4. Check if the file exists before attempting to send
    fs.access(filePath, fs.constants.F_OK, (err) => {
        if (err) {
            return res.status(404).send('File not found.');
        }

        // 5. Stream the file back to the user
        res.sendFile(filePath, (err) => {
            if (err) {
                console.error(err);
                res.status(500).send('Error downloading file.');
            }
        });
    });
});

app.listen(3000, () => console.log('Server running on port 3000'));