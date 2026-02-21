<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SchedulesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'ល.រ',
            'ប្រធានបទកិច្ចប្រជុំ',
            'ថ្ងៃខែឆ្នាំ',
            'ម៉ោងអនុវត្ត',
            'ទីកន្លែង/បន្ទប់',
            'ស្ថានភាពបច្ចុប្បន្ន'
        ];
    }

    public function map($row): array
    {
        $now = Carbon::now();
        
        $date = !empty($row['date']) ? Carbon::parse($row['date']) : null;
        $startTime = !empty($row['start_time']) ? Carbon::parse($row['start_time']) : null;
        $endTime = !empty($row['end_time']) ? Carbon::parse($row['end_time']) : null;

        $timeDisplay = 'N/A';
        if ($startTime) {
            $startPeriod = $startTime->hour < 12 ? 'ព្រឹក' : 'រសៀល';
            $timeDisplay = $startTime->format('H:i') . ' ' . $startPeriod;
            
            if ($endTime) {
                $endPeriod = $endTime->hour < 12 ? 'ព្រឹក' : 'រសៀល';
                $timeDisplay .= ' - ' . $endTime->format('H:i') . ' ' . $endPeriod;
            }
        }

        $statusText = 'មិនទាន់ប្រជុំ';
        
        if ($date) {
            if ($date->isToday()) {
                if ($startTime && $endTime) {
                    $fullStart = Carbon::parse($date->format('Y-m-d') . ' ' . $startTime->format('H:i:s'));
                    $fullEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $endTime->format('H:i:s'));

                    if ($now->between($fullStart, $fullEnd)) {
                        $statusText = 'កំពុងប្រជុំ';
                    } elseif ($now->gt($fullEnd)) {
                        $statusText = 'បានបញ្ចប់';
                    }
                }
            } elseif ($date->isPast()) {
                $statusText = 'បានបញ្ចប់';
            } elseif ($date->isFuture()) {
                $statusText = 'មិនទាន់ប្រជុំ';
            }
        }

        return [
            $row['id'] ?? '',
            $row['title'] ?? 'គ្មានចំណងជើង',
            $date ? $date->format('d-m-Y') : 'N/A',
            $timeDisplay,
            $row['location'] ?? $row['room'] ?? 'N/A',
            $statusText
        ];
    }
}
