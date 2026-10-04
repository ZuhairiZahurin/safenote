<?php

namespace App\Support;

/**
 * The list of classes the school runs, built from config/school.php, so that
 * every form offers the same options and validation accepts nothing else.
 */
class SchoolClasses
{
    /**
     * Every class in the school, in form order: "1 Imtiyaz", "1 Karisma", ...
     *
     * @return list<string>
     */
    public static function all(): array
    {
        $classes = [];

        foreach (config('school.forms') as $form) {
            foreach (config('school.class_names') as $name) {
                $classes[] = "{$form} {$name}";
            }
        }

        return $classes;
    }

    /**
     * Grouped by form level, for a grouped <select> menu.
     *
     * @return array<string, list<string>>
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (config('school.forms') as $form) {
            foreach (config('school.class_names') as $name) {
                $grouped["Form {$form}"][] = "{$form} {$name}";
            }
        }

        return $grouped;
    }
}
