import express from 'express';
import multer from 'multer';
import path from 'path';
import pkg from 'pdf-to-printer';
import fs from 'fs';
import cors from 'cors';

const app = express();
const port = 3001;
const { print } = pkg;

const upload = multer({ dest: 'uploads/' });

app.use(cors());

app.post('/print', upload.single('file'), async (req, res) => {
  const pdfPath = req.file.path;
  const originalName = req.file.originalname;
  const safePath = path.resolve(pdfPath);

  try {
    await print(safePath, {
      printer: 'Zebra ZC300 USB Card Printer',
      win32: ['-print-settings "fit"']
    });

    res.status(200).send({ status: 'success', message: 'Printed successfully.' });
  } catch (err) {
    console.error('Print error:', err);
    res.status(500).send({ status: 'error', message: 'Failed to print.', details: err.message });
  } finally {
    fs.unlink(safePath, () => {}); // Clean up temp file
  }
});

app.listen(port, () => {
  console.log(`Zebra Print API running at http://localhost:${port}`);
});
