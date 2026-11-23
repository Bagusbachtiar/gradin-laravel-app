<?php

namespace App\Http\Controllers;

use App\Repositories\CourierRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    private $courierRepository;

    public function __construct(CourierRepository $courierRepository)
    {
        $this->courierRepository = $courierRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $couriers = $this->courierRepository->getPaginate($request->all());

        return response()->json($couriers);
    }

    public function show(int $id): JsonResponse
    {
        $courier = $this->courierRepository->findById($id);

        return response()->json([
            'data' => $courier,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $isSuccess = $this->courierRepository->store($request);

        if ($isSuccess['status'] == 'success') {
            return response()->json($isSuccess, 201);
        } else {
            return response()->json($isSuccess, 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $isSuccess = $this->courierRepository->update($request, $id);

        if ($isSuccess['status'] == 'success') {
            return response()->json($isSuccess, 200);
        } else {
            return response()->json($isSuccess, 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $isSuccess = $this->courierRepository->destroy($id);

        if ($isSuccess) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Courier deleted successfully.',
            ], 200);
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => 'Courier not found or failed to delete.',
            ], 404);
        }
    }
}
