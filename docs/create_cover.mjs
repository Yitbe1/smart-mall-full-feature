import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
         AlignmentType, WidthType, ShadingType, BorderStyle, ImageRun,
         VerticalAlign } from 'docx';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUTPUT = path.join(__dirname, 'cover_page.docx');
const LOGO_PATH = path.join(__dirname, '..', 'assets', 'images', 'logo-icon.png');

const C = {
  TEAL: "45A8AA", NAVY: "0F172A", NV1: "1E293B", NV2: "334155", NV3: "475569",
  BLUE: "2563EB", WHITE: "FFFFFF", MUTED: "94A3B8", SLATE: "64748B",
  DARK: "0F172A", AW: "F8FAFC", LG: "E2E8F0",
};
const A4_W = 11906;
const LOGO_DATA = fs.readFileSync(LOGO_PATH);

function tcell(children, opts = {}) {
  return new TableCell({
    width: { size: opts.w || A4_W, type: WidthType.DXA },
    shading: opts.fill ? { fill: opts.fill, type: ShadingType.CLEAR } : undefined,
    verticalAlign: opts.val || VerticalAlign.TOP,
    margins: opts.m || { top: 0, bottom: 0, left: 0, right: 0 },
    children: children.length ? children : [new Paragraph({ spacing: { before: 0, after: 0 }, children: [] })],
  });
}

function oneCell(fill) {
  return tcell([], { fill });
}

function tx(text, opts = {}) {
  return new TextRun({
    text, bold: opts.b || false, size: opts.s || 24,
    color: opts.c || C.DARK, font: 'Times New Roman',
  });
}

function para(children, opts = {}) {
  return new Paragraph({
    children,
    alignment: opts.a || AlignmentType.LEFT,
    spacing: { before: opts.b || 0, after: opts.e || 0 },
    border: opts.border || undefined,
  });
}

const logoPara = para([new ImageRun({
  type: 'png', data: LOGO_DATA,
  transformation: { width: 90, height: 90 },
  altText: { title: 'Logo', descrption: 'Smart Mall Logo', name: 'Logo' },
})], { a: AlignmentType.CENTER, b: 300 });

const brandPara = para([
  tx('Smart Mall ', { b: true, s: 140, c: C.WHITE }),
  tx('\u25CF', { s: 140, c: C.TEAL }),
], { a: AlignmentType.CENTER, b: 0 });

const tagPara = para([tx('Smart . Secure . Stylish', { s: 32, c: C.TEAL })], { a: AlignmentType.CENTER, b: 60 });

const divPara = para([], {
  a: AlignmentType.CENTER, b: 150,
  border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: C.TEAL, space: 1 } },
});

const titlePara = para([tx('FINAL YEAR PROJECT', { b: true, s: 80, c: C.WHITE })], { a: AlignmentType.CENTER, b: 150 });

const subPara = para([tx('Documentation & Implementation', { s: 44, c: C.MUTED })], { a: AlignmentType.CENTER, b: 60 });

const infoContent = [
  para([tx('PROJECT REPORT', { b: true, s: 24, c: C.BLUE })]),
  para([], { border: { bottom: { style: BorderStyle.SINGLE, size: 2, color: C.LG, space: 1 } } }),
  para([tx('Submitted by:', { b: true, s: 36, c: C.DARK })], { b: 60 }),
  para([tx('[Student Full Name]', { b: true, s: 36, c: C.DARK })]),
  para([tx('[Student ID / Registration Number]', { s: 36, c: C.SLATE })]),
  para([], { b: 40 }),
  para([tx('Department of Computer Science', { s: 36, c: C.SLATE })]),
  para([tx('[University Name]', { s: 36, c: C.SLATE })]),
];

const infoTable = new Table({
  width: { size: A4_W, type: WidthType.DXA },
  columnWidths: [300, 11606],
  rows: [new TableRow({
    children: [
      tcell([], { w: 300, fill: C.BLUE }),
      tcell(infoContent, { w: 11606, m: { top: 60, bottom: 40, left: 200, right: 200 } }),
    ],
  })],
});

const stepCount = 20;
const gradCells = Array.from({ length: stepCount }, (_, i) => {
  const ratio = i / (stepCount - 1);
  const r = Math.round(0x25 + (0x45 - 0x25) * ratio);
  const g = Math.round(0x63 + (0xA8 - 0x63) * ratio);
  const b = Math.round(0xEB + (0xAA - 0xEB) * ratio);
  const hex = r.toString(16).padStart(2, '') + g.toString(16).padStart(2, '') + b.toString(16).padStart(2, '');
  return tcell([], { w: Math.floor(A4_W / stepCount), fill: hex.toUpperCase() });
});

const gradTable = new Table({
  width: { size: A4_W, type: WidthType.DXA },
  rows: [new TableRow({ children: gradCells, height: { value: 200, rule: 'exact' } })],
});

const mainRows = [
  new TableRow({ children: [oneCell(C.TEAL)], height: { value: 120, rule: 'exact' } }),
  new TableRow({
    children: [tcell([logoPara, brandPara, tagPara, divPara, titlePara, subPara], { fill: C.NAVY, val: VerticalAlign.CENTER })],
  }),
  new TableRow({ children: [oneCell(C.NV1)], height: { value: 1200, rule: 'exact' } }),
  new TableRow({ children: [oneCell(C.NV2)], height: { value: 700, rule: 'exact' } }),
  new TableRow({ children: [oneCell(C.NV3)], height: { value: 400, rule: 'exact' } }),
  new TableRow({ children: [oneCell(C.AW)], height: { value: 200, rule: 'exact' } }),
  new TableRow({ children: [tcell([infoTable])] }),
  new TableRow({ children: [tcell([para([tx('May 2026', { s: 36, c: C.SLATE })], { a: AlignmentType.RIGHT, b: 100 })])] }),
  new TableRow({ children: [tcell([para([], {
    a: AlignmentType.CENTER,
    border: { top: { style: BorderStyle.SINGLE, size: 2, color: C.LG, space: 0 } },
  })])] }),
  new TableRow({ children: [oneCell(C.LG)], height: { value: 40, rule: 'exact' } }),
  new TableRow({ children: [tcell([gradTable])], height: { value: 150, rule: 'exact' } }),
];

const doc = new Document({
  styles: {
    default: {
      document: { run: { font: 'Times New Roman', size: 24 } },
    },
    paragraphStyles: [
      {
        id: 'Normal',
        name: 'Normal',
        run: { font: 'Times New Roman', size: 24 },
        paragraph: { spacing: { after: 0, before: 0 } },
      },
      ...[1, 2, 3, 4].map(i => ({
        id: `Heading${i}`,
        name: `heading ${i}`,
        basedOn: 'Normal',
        run: { font: 'Times New Roman' },
      })),
    ],
  },
  sections: [{
    properties: {
      page: {
        size: { width: A4_W, height: 16838 },
        margin: { top: 0, bottom: 0, left: 0, right: 0 },
      },
    },
    children: [
      new Table({
        width: { size: A4_W, type: WidthType.DXA },
        columnWidths: [A4_W],
        rows: mainRows,
      }),
    ],
  }],
});

Packer.toBuffer(doc).then(buf => {
  fs.writeFileSync(OUTPUT, buf);
  console.log(`Cover docx saved: ${OUTPUT}`);
  console.log(`Size: ${(buf.length / 1024).toFixed(0)} KB`);
}).catch(err => { console.error(err); process.exit(1); });
