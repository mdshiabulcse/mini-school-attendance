<?php
// database/seeders/StudentSeeder.php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            // Class 1 - Section A
            [
                'student_id' => 'STU2024001',
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@student.com',
                'class' => '1',
                'section' => 'A',
                'roll_number' => '1',
                'date_of_birth' => '2017-03-15',
                'gender' => 'female',
                'parent_name' => 'Robert Johnson',
                'parent_email' => 'robert@parent.com',
                'parent_phone' => '+1234567894',
                'address' => '123 Main Street, City, State',
            ],
            [
                'student_id' => 'STU2024002',
                'name' => 'Bob Smith',
                'email' => 'bob.smith@student.com',
                'class' => '1',
                'section' => 'A',
                'roll_number' => '2',
                'date_of_birth' => '2017-05-20',
                'gender' => 'male',
                'parent_name' => 'Mary Smith',
                'parent_email' => 'mary.smith@parent.com',
                'parent_phone' => '+1234567896',
                'address' => '456 Oak Avenue, City, State',
            ],
            [
                'student_id' => 'STU2024003',
                'name' => 'Carol Davis',
                'email' => 'carol.davis@student.com',
                'class' => '1',
                'section' => 'A',
                'roll_number' => '3',
                'date_of_birth' => '2017-07-10',
                'gender' => 'female',
                'parent_name' => 'James Davis',
                'parent_email' => 'james.davis@parent.com',
                'parent_phone' => '+1234567897',
                'address' => '789 Pine Road, City, State',
            ],

            // Class 1 - Section B
            [
                'student_id' => 'STU2024004',
                'name' => 'David Wilson',
                'email' => 'david.wilson@student.com',
                'class' => '1',
                'section' => 'B',
                'roll_number' => '1',
                'date_of_birth' => '2017-02-28',
                'gender' => 'male',
                'parent_name' => 'Lisa Wilson',
                'parent_email' => 'lisa@parent.com',
                'parent_phone' => '+1234567895',
                'address' => '321 Elm Street, City, State',
            ],
            [
                'student_id' => 'STU2024005',
                'name' => 'Eva Brown',
                'email' => 'eva.brown@student.com',
                'class' => '1',
                'section' => 'B',
                'roll_number' => '2',
                'date_of_birth' => '2017-04-12',
                'gender' => 'female',
                'parent_name' => 'Thomas Brown',
                'parent_email' => 'thomas.brown@parent.com',
                'parent_phone' => '+1234567898',
                'address' => '654 Maple Drive, City, State',
            ],

            // Class 2 - Section A
            [
                'student_id' => 'STU2024006',
                'name' => 'Frank Miller',
                'email' => 'frank.miller@student.com',
                'class' => '2',
                'section' => 'A',
                'roll_number' => '1',
                'date_of_birth' => '2016-08-25',
                'gender' => 'male',
                'parent_name' => 'Nancy Miller',
                'parent_email' => 'nancy.miller@parent.com',
                'parent_phone' => '+1234567899',
                'address' => '987 Cedar Lane, City, State',
            ],
            [
                'student_id' => 'STU2024007',
                'name' => 'Grace Taylor',
                'email' => 'grace.taylor@student.com',
                'class' => '2',
                'section' => 'A',
                'roll_number' => '2',
                'date_of_birth' => '2016-11-30',
                'gender' => 'female',
                'parent_name' => 'Paul Taylor',
                'parent_email' => 'paul.taylor@parent.com',
                'parent_phone' => '+1234567800',
                'address' => '147 Birch Street, City, State',
            ],

            // Class 3 - Section A
            [
                'student_id' => 'STU2024008',
                'name' => 'Henry Anderson',
                'email' => 'henry.anderson@student.com',
                'class' => '3',
                'section' => 'A',
                'roll_number' => '1',
                'date_of_birth' => '2015-01-15',
                'gender' => 'male',
                'parent_name' => 'Susan Anderson',
                'parent_email' => 'susan.anderson@parent.com',
                'parent_phone' => '+1234567801',
                'address' => '258 Walnut Avenue, City, State',
            ],
            [
                'student_id' => 'STU2024009',
                'name' => 'Ivy Martinez',
                'email' => 'ivy.martinez@student.com',
                'class' => '3',
                'section' => 'A',
                'roll_number' => '2',
                'date_of_birth' => '2015-06-22',
                'gender' => 'female',
                'parent_name' => 'Carlos Martinez',
                'parent_email' => 'carlos.martinez@parent.com',
                'parent_phone' => '+1234567802',
                'address' => '369 Spruce Road, City, State',
            ],
            [
                'student_id' => 'STU2024010',
                'name' => 'Jack Thomas',
                'email' => 'jack.thomas@student.com',
                'class' => '3',
                'section' => 'A',
                'roll_number' => '3',
                'date_of_birth' => '2015-09-08',
                'gender' => 'male',
                'parent_name' => 'Maria Thomas',
                'parent_email' => 'maria.thomas@parent.com',
                'parent_phone' => '+1234567803',
                'address' => '741 Oak Street, City, State',
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }

        $this->command->info('Students created successfully!');
        $this->command->info('Total students: ' . count($students));
    }
}
