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
  const pdfPath = path.resolve(req.file.path);

  // NEVEZD MEG a nyomtatódat pontosan, amit a `lpstat -p` listáz (pl. zebra_zc300)
  const printerName = 'zebra_zc300';

  const cmd = `lp -d ${printerName} "${pdfPath}"`;

  exec(cmd, (error, stdout, stderr) => {
    fs.unlink(pdfPath, () => {}); // törli a fájlt akkor is, ha hibás volt

    if (error) {
      console.error('Hiba a nyomtatás közben:', error);
      return res.status(500).json({ status: 'error', message: stderr || error.message });
    }

    res.status(200).json({ status: 'success', message: stdout.trim() });
  });
});

app.listen(port, () => {
  console.log(`Zebra Print API running at http://localhost:${port}`);
});