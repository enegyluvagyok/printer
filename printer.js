import express from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { exec } from 'child_process';
import cors from 'cors';

const app = express();
const port = 3001;

const upload = multer({ dest: 'uploads/' });

app.use(cors());

app.post('/print', upload.single('file'), async (req, res) => {
  if (!req.file) {
    return res.status(400).json({ status: 'error', message: 'Nincs feltöltött fájl.' });
  }

  const pdfPath = path.resolve(req.file.path);
  const printerName = 'ZTC_ZC300';
  const cmd = `lp -d ${printerName} "${pdfPath}"`;

  exec(cmd, (error, stdout, stderr) => {
    if (error) {
      console.error('Nyomtatási hiba:', error);
      return res.status(500).json({
        status: 'error',
        message: stderr || error.message,
      });
    }

    // Nyomtatás sikeres, ezután törlünk
    fs.unlink(pdfPath, (unlinkErr) => {
      if (unlinkErr) {
        console.warn(`Nem sikerült törölni a fájlt: ${pdfPath}`, unlinkErr);
      }
    });

    res.status(200).json({
      status: 'success',
      message: 'Nyomtatás sikeresen elindítva.',
      output: stdout.trim()
    });
  });
});

app.listen(port, () => {
  console.log(`✅ Zebra Print API running at http://localhost:${port}`);
});
