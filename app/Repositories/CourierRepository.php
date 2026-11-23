<?php

namespace App\Repositories;

use App\Models\Courier; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourierRepository 
{
    private $model;

    public function __construct(Courier $courier)
    {
    $this->model = $courier;
    }

    public function get()
    {
        $data = $this->model::get();
        return $data;
    }
    

public function getPaginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // 1. Multi-word search (?search=budi+agung -> matches "Budiono Hadi Agung")
        if (! empty($filters['search'])) {
            $keywords = preg_split('/\s+/', trim((string) $filters['search']), -1, PREG_SPLIT_NO_EMPTY);

            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->where('name', 'like', '%' . $word . '%');
                }
            });
        }

        // 2. Filter by level (?level=2,3)
        if (! empty($filters['level'])) {
            $levels = is_array($filters['level'])
                ? $filters['level']
                : explode(',', (string) $filters['level']);

            $validLevels = array_filter(
                array_map('intval', $levels),
                fn (int $level) => $level >= 1 && $level <= 5
            );

            if (! empty($validLevels)) {
                $query->whereIn('level', $validLevels);
            }
        }

        // 3. Sorting: Default by 'name' (asc), override by registration date ('created_at')
        $sortBy = $filters['sort_by'] ?? $filters['sort'] ?? 'name';
        $direction = strtolower((string) ($filters['sort_dir'] ?? ''));

        if ($sortBy === 'created_at') {
            $dir = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';
            $query->orderBy('created_at', $dir);
        } else {
            $dir = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';
            $query->orderBy('name', $dir);
        }

        // 4. Pagination (preserving query string for frontend links)
        $limit = ! empty($filters['per_page']) ? (int) $filters['per_page'] : $perPage;

        return $query->paginate($limit)->withQueryString();
    }

    public function findById(int $id): Courier
    {
        return $this->model->findOrFail($id);
    }

    public function store(Request $request): array
{
    try {
        $validator = Validator::make($request->all(), [
            'code'      => 'required|string|max:20|unique:couriers,code',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:couriers,email',
            'phone'     => 'required|string|max:20|unique:couriers,phone',
            'level'     => 'required|integer|between:1,5',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        try {
            $courier = $this->model->create([
                'code'      => $request->code,
                'name'      => $request->name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'level'     => $request->level,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return [
                'status'  => 'success',
                'message' => 'Data stored successfully.',
                'data'    => $courier,
            ];
        } catch (\Throwable $th) {
            return [
                'status'  => 'error',
                'message' => 'Error on create data: ' . $th->getMessage(),
            ];
        }
    } catch (\Throwable $th) {
        return [
            'status'  => 'error',
            'message' => $th->getMessage(),
        ];
    }
}

public function update(Request $request, int $id): array
{
    try {
        $courier = $this->model->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code'      => ['sometimes', 'required', 'string', 'max:20', Rule::unique('couriers', 'code')->ignore($id)],
            'name'      => 'sometimes|required|string|max:255',
            'email'     => ['sometimes', 'required', 'email', 'max:255', Rule::unique('couriers', 'email')->ignore($id)],
            'phone'     => ['sometimes', 'required', 'string', 'max:20', Rule::unique('couriers', 'phone')->ignore($id)],
            'level'     => 'sometimes|required|integer|between:1,5',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        try {
            $courier->update($validator->validated());

            return [
                'status'  => 'success',
                'message' => 'Data updated successfully.',
                'data'    => $courier->fresh(),
            ];
        } catch (\Throwable $th) {
            return [
                'status'  => 'error',
                'message' => 'Error on update data: ' . $th->getMessage(),
            ];
        }
    } catch (\Throwable $th) {
        return [
            'status'  => 'error',
            'message' => $th->getMessage(),
        ];
    }
}

public function destroy(int $id): bool
{
    try {
        $courier = $this->model->findOrFail($id);
        return (bool) $courier->delete();
    } catch (\Throwable $th) {
        return false;
    }
}

}