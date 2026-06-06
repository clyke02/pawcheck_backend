<?php

namespace Database\Seeders;

use App\Models\Breed;
use Illuminate\Database\Seeder;

class BreedSeeder extends Seeder
{
    public function run(): void
    {
        $breeds = [
            // === CATS (12) ===
            ['name' => 'Abyssinian',       'species' => 'cat', 'ideal_weight_male' => 4.50, 'ideal_weight_female' => 3.50],
            ['name' => 'Bengal',           'species' => 'cat', 'ideal_weight_male' => 6.00, 'ideal_weight_female' => 4.50],
            ['name' => 'Birman',           'species' => 'cat', 'ideal_weight_male' => 5.50, 'ideal_weight_female' => 4.50],
            ['name' => 'Bombay',           'species' => 'cat', 'ideal_weight_male' => 5.00, 'ideal_weight_female' => 4.00],
            ['name' => 'British Shorthair','species' => 'cat', 'ideal_weight_male' => 7.00, 'ideal_weight_female' => 5.00],
            ['name' => 'Egyptian Mau',     'species' => 'cat', 'ideal_weight_male' => 4.50, 'ideal_weight_female' => 3.50],
            ['name' => 'Maine Coon',       'species' => 'cat', 'ideal_weight_male' => 8.00, 'ideal_weight_female' => 5.50],
            ['name' => 'Persian',          'species' => 'cat', 'ideal_weight_male' => 5.00, 'ideal_weight_female' => 4.00],
            ['name' => 'Ragdoll',          'species' => 'cat', 'ideal_weight_male' => 8.00, 'ideal_weight_female' => 6.00],
            ['name' => 'Russian Blue',     'species' => 'cat', 'ideal_weight_male' => 5.00, 'ideal_weight_female' => 4.00],
            ['name' => 'Siamese',          'species' => 'cat', 'ideal_weight_male' => 4.50, 'ideal_weight_female' => 3.50],
            ['name' => 'Sphynx',           'species' => 'cat', 'ideal_weight_male' => 5.00, 'ideal_weight_female' => 3.50],

            // === DOGS (25) ===
            ['name' => 'American Bulldog',           'species' => 'dog', 'ideal_weight_male' => 36.00, 'ideal_weight_female' => 27.00],
            ['name' => 'American Pit Bull Terrier',  'species' => 'dog', 'ideal_weight_male' => 27.00, 'ideal_weight_female' => 22.00],
            ['name' => 'Basset Hound',               'species' => 'dog', 'ideal_weight_male' => 28.00, 'ideal_weight_female' => 23.00],
            ['name' => 'Beagle',                     'species' => 'dog', 'ideal_weight_male' => 11.00, 'ideal_weight_female' => 10.00],
            ['name' => 'Boxer',                      'species' => 'dog', 'ideal_weight_male' => 30.00, 'ideal_weight_female' => 25.00],
            ['name' => 'Chihuahua',                  'species' => 'dog', 'ideal_weight_male' =>  2.50, 'ideal_weight_female' =>  2.50],
            ['name' => 'English Cocker Spaniel',     'species' => 'dog', 'ideal_weight_male' => 14.00, 'ideal_weight_female' => 13.00],
            ['name' => 'English Setter',             'species' => 'dog', 'ideal_weight_male' => 29.00, 'ideal_weight_female' => 25.00],
            ['name' => 'German Shorthaired',         'species' => 'dog', 'ideal_weight_male' => 27.00, 'ideal_weight_female' => 23.00],
            ['name' => 'Great Pyrenees',             'species' => 'dog', 'ideal_weight_male' => 52.00, 'ideal_weight_female' => 41.00],
            ['name' => 'Havanese',                   'species' => 'dog', 'ideal_weight_male' =>  5.00, 'ideal_weight_female' =>  4.50],
            ['name' => 'Japanese Chin',              'species' => 'dog', 'ideal_weight_male' =>  3.50, 'ideal_weight_female' =>  3.50],
            ['name' => 'Keeshond',                   'species' => 'dog', 'ideal_weight_male' => 20.00, 'ideal_weight_female' => 17.00],
            ['name' => 'Leonberger',                 'species' => 'dog', 'ideal_weight_male' => 60.00, 'ideal_weight_female' => 45.00],
            ['name' => 'Miniature Pinscher',         'species' => 'dog', 'ideal_weight_male' =>  4.50, 'ideal_weight_female' =>  4.00],
            ['name' => 'Newfoundland',               'species' => 'dog', 'ideal_weight_male' => 68.00, 'ideal_weight_female' => 54.00],
            ['name' => 'Pomeranian',                 'species' => 'dog', 'ideal_weight_male' =>  2.50, 'ideal_weight_female' =>  2.00],
            ['name' => 'Pug',                        'species' => 'dog', 'ideal_weight_male' =>  8.50, 'ideal_weight_female' =>  7.50],
            ['name' => 'Saint Bernard',              'species' => 'dog', 'ideal_weight_male' => 82.00, 'ideal_weight_female' => 64.00],
            ['name' => 'Samoyed',                    'species' => 'dog', 'ideal_weight_male' => 25.00, 'ideal_weight_female' => 18.00],
            ['name' => 'Scottish Terrier',           'species' => 'dog', 'ideal_weight_male' =>  9.50, 'ideal_weight_female' =>  9.00],
            ['name' => 'Shiba Inu',                  'species' => 'dog', 'ideal_weight_male' => 11.00, 'ideal_weight_female' =>  8.00],
            ['name' => 'Staffordshire Bull Terrier', 'species' => 'dog', 'ideal_weight_male' => 16.00, 'ideal_weight_female' => 13.00],
            ['name' => 'Wheaten Terrier',            'species' => 'dog', 'ideal_weight_male' => 18.00, 'ideal_weight_female' => 14.50],
            ['name' => 'Yorkshire Terrier',          'species' => 'dog', 'ideal_weight_male' =>  3.00, 'ideal_weight_female' =>  3.00],
        ];

        foreach ($breeds as $breed) {
            Breed::create($breed);
        }
    }
}
