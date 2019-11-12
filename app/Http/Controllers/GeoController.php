<?php

namespace App\Http\Controllers;

use App\Geo;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Exceptions\GeoException;
use Log;

class GeoController extends Controller
{
    /** @var GeoService */
    protected $service;

    /**
     * GeoController constructor.
     * @param GeoService $service
     */
    public function __construct(GeoService $service)
    {
        $this->service = $service;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function index()
    {
        return $this->service->all();
    }

    /**
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        return Geo::findOrFail($id);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $data = $this->service->validate($request, true);
        /** @var Geo $geo */
        $geo = Geo::create($data);
        return response()->json([
            'status' => env('APP_STATUS_OK_TEXT'),
            'result' => 'created',
            'id' => $geo->id,
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, int $id)
    {
        return response()->json([
            $this->service->update($request, $id)
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeoException
     */
    public function destroy(Request $request, int $id)
    {
        /** @var Geo $geo */
        $geo = Geo::findOrFail($id);

        if ($request->archive === 'true') {
            $messageOK = 'archived';
            $method = 'delete';
            $verb = 'archive';
        } else {
            $messageOK = 'deleted';
            $method = 'forceDelete';
            $verb = 'delete';
        }
        $messageError = 'not ' . $messageOK;

        try {
            if ($geo->$method()) {
                return response()->json([
                    'status' => env('APP_STATUS_OK_TEXT'),
                    'result' => $messageOK,
                ]);
            } else {
                throw new GeoException($messageError . ' (can\'t ' . $verb . ')');
            }
        } catch (GeoException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            if ($e instanceof QueryException) {
                $messageError .= ' (DB)';
            }
            throw new GeoException($messageError);
        }
    }
}
