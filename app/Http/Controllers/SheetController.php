<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sheet;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SheetController extends Controller
{
   # public function index()
   # {
     #   return view('index', [
       #     'sheets' => Sheet::latest()->get()
      #  ]);

        # return "Laravel is working!";
   # }

   public function index()
{
    try {
        return Sheet::latest()->get();
    } catch (\Throwable $e) {
        return [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
} 

    /**
     * UPLOAD (ONLY STORE FILE PATH — NO PARSING)
     */
    public function upload(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '4096M');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads');

        $sheet = Sheet::create([
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'data' => null
        ]);

        return redirect()->route('sheet.edit', $sheet->id);
    }

    /**
     * EDIT (SAFE + CONSISTENT MULTI SHEET LOADING)
     */
    public function edit($id)
    {
        set_time_limit(0);
        ini_set('memory_limit', '4096M');

        $sheet = Sheet::findOrFail($id);
        $path = Storage::path($sheet->file_path);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);

        $allSheets = [];

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {

            $rows = $worksheet->toArray(null, true, true, false);

            $celldata = [];

            foreach ($rows as $r => $row) {
                foreach ($row as $c => $value) {

                    if ($value === null || $value === '') continue;

                    $celldata[] = [
                        'r' => $r,
                        'c' => $c,
                        'v' => $value
                    ];
                }
            }

            $allSheets[] = [
                'name' => $worksheet->getTitle(),
                'celldata' => $celldata,
                'row' => count($rows),
                'column' => isset($rows[0]) ? count($rows[0]) : 0
            ];
        }

        return view('sheet', [
            'sheet' => $sheet,
            'data' => $allSheets
        ]);
    }

    /**
     * SAVE (FULL SAFE VERSION — FIXES NEW UPLOAD ISSUE)
     */
    public function save(Request $request, $id)
    {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '4096M');

            $sheet = Sheet::findOrFail($id);
            $path = Storage::path($sheet->file_path);

            if (!file_exists($path)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            $data = $request->input('data', []);

            if (!is_array($data)) {
                return response()->json(['message' => 'Invalid data'], 422);
            }

            $spreadsheet = IOFactory::load($path);

            foreach ($data as $index => $sheetData) {

                if (!isset($sheetData['celldata']) || !is_array($sheetData['celldata'])) {
                    continue;
                }

                // 🔥 SAFE SHEET ACCESS (NO CRASH ON NEW FILES)
                if (!$spreadsheet->sheetNameExists($sheetData['name'] ?? '')) {
                    continue;
                }

                $worksheet = $spreadsheet->getSheet($index);

                if (!$worksheet) continue;

                foreach ($sheetData['celldata'] as $cell) {

                    if (!isset($cell['r'], $cell['c'])) continue;

                    $row = $cell['r'] + 1;
                    $col = $cell['c'] + 1;

                    $coord = Coordinate::stringFromColumnIndex($col) . $row;

                    $value = $cell['v'] ?? '';

                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    $worksheet->setCellValue($coord, (string)$value);
                }
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save($path);

            return response()->json([
                'status' => 'success',
                'message' => 'Saved successfully'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * DOWNLOAD
     */
    public function download($id)
    {
        $sheet = Sheet::findOrFail($id);
        $path = Storage::path($sheet->file_path);

        return response()->download($path, $sheet->name);
    }
}