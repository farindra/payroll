<?php

namespace App\Services;

class Utf8CleanerService
{
    /**
     * Clean UTF-8 encoding from any data structure
     */
    public function clean($data)
    {
        if (is_string($data)) {
            return $this->cleanString($data);
        } elseif (is_array($data)) {
            return $this->cleanArray($data);
        } elseif (is_object($data)) {
            return $this->cleanObject($data);
        }

        return $data;
    }

    /**
     * Clean a string for UTF-8 encoding
     */
    public function cleanString($string)
    {
        if (empty($string)) {
            return $string;
        }

        // First, try to detect and fix invalid UTF-8
        if (!$this->isValidUtf8($string)) {
            // Try to convert from common encodings
            $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

            // If still invalid, try aggressive cleaning
            if (!$this->isValidUtf8($string)) {
                $string = $this->aggressiveClean($string);
            }
        }

        return $string;
    }

    /**
     * Clean an array recursively
     */
    private function cleanArray($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        return array_map([$this, 'clean'], $array);
    }

    /**
     * Clean an object recursively
     */
    private function cleanObject($object)
    {
        if (!is_object($object)) {
            return $object;
        }

        $cleanedObject = new \stdClass();
        foreach (get_object_vars($object) as $key => $value) {
            $cleanedKey = $this->cleanString($key);
            $cleanedObject->$cleanedKey = $this->clean($value);
        }

        return $cleanedObject;
    }

    /**
     * Check if string is valid UTF-8
     */
    private function isValidUtf8($string)
    {
        return mb_check_encoding($string, 'UTF-8');
    }

    /**
     * Aggressive UTF-8 cleaning for severely corrupted strings
     */
    private function aggressiveClean($string)
    {
        // Remove invalid UTF-8 sequences
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        // Remove non-printable characters except basic punctuation
        $string = preg_replace('/[^\x20-\x7E\x0A\x0D]/u', '', $string);

        // If the string is empty after cleaning, return a placeholder
        if (empty($string)) {
            return '[Invalid UTF-8 data]';
        }

        return $string;
    }

    /**
     * Clean payroll data specifically
     */
    public function cleanPayrollData($payrollData)
    {
        if (!is_array($payrollData)) {
            return $payrollData;
        }

        // Clean specific payroll fields
        foreach ($payrollData as $key => $value) {
            switch ($key) {
                case 'earnings':
                case 'deductions':
                    if (is_array($value)) {
                        $payrollData[$key] = $this->cleanPayrollComponents($value);
                    }
                    break;
                case 'employee_name':
                case 'period_name':
                case 'department_name':
                case 'position':
                    $payrollData[$key] = $this->cleanString($value);
                    break;
                default:
                    $payrollData[$key] = $this->clean($value);
                    break;
            }
        }

        return $payrollData;
    }

    /**
     * Clean payroll components (earnings/deductions)
     */
    public function cleanPayrollComponents($components)
    {
        if (!is_array($components)) {
            return $components;
        }

        return array_map(function ($component) {
            if (is_array($component)) {
                foreach ($component as $key => $value) {
                    if ($key === 'name') {
                        $component[$key] = $this->cleanString($value);
                    } else {
                        $component[$key] = $this->clean($value);
                    }
                }
            }
            return $component;
        }, $components);
    }
}