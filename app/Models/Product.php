<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
        use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'name','slug','description','expected_results',
        'usage_instructions','time_of_use','shelf_life',
        'incompatible_products','image','recommended_for'
    ];

    public function recommendedByDoctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_product', 'product_id', 'doctor_id');
    }
    public function usersInRoutine()
{
    return $this->belongsToMany(User::class, 'routines', 'product_id', 'user_id');
}

}
