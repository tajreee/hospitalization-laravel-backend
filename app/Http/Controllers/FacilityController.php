<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreFacilityRequest;
use App\Models\Facility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class FacilityController extends Controller
{
    // Cache key constants
    const FACILITIES_CACHE_KEY = 'facilities_all';
    const CACHE_TTL = 3600; // 1 hour in seconds

    public function store(StoreFacilityRequest $request) {
        try {
            return DB::transaction(function () use ($request) {
                $facility = Facility::create([
                    'name' => $request->name,
                    'fee'  => $request->fee,
                ]);

                // Clear cache when new facility is created
                $this->clearFacilitiesCache();

                return response()->json([
                    'success' => true,
                    'status'  => 201,
                    'message' => 'Facility created successfully.',
                    'data'    => [
                        'facility' => $facility->only(['id', 'name', 'fee']),
                    ]
                ], 201);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to create facility.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function facilities(Request $request)
    {
        try {
            // Try to get from cache first
            $cacheKey = self::FACILITIES_CACHE_KEY;
            
            // Check if data exists in cache
            $facilities = Cache::remember($cacheKey, self::CACHE_TTL, function () {
                return Facility::all()->map(function ($facility) {
                    return $facility->only(['id', 'name', 'fee']);
                });
            });

            // Alternative using Redis directly (commented, use Cache::remember above)
            /*
            $cachedFacilities = Redis::get($cacheKey);
            
            if ($cachedFacilities) {
                $facilities = json_decode($cachedFacilities, true);
            } else {
                $facilities = Facility::all()->map(function ($facility) {
                    return $facility->only(['id', 'name', 'fee']);
                });
                
                // Cache for 1 hour
                Redis::setex($cacheKey, self::CACHE_TTL, json_encode($facilities));
            }
            */

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'Facilities retrieved successfully.',
                'data'    => [
                    'facilities' => $facilities,
                    'from_cache' => Cache::has($cacheKey), // Debug info
                    'cache_info' => [
                        'cache_key' => $cacheKey,
                        'ttl_seconds' => self::CACHE_TTL,
                        'cached_at' => now()->toDateTimeString()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            // If cache fails, fallback to database
            $facilities = Facility::all()->map(function ($facility) {
                return $facility->only(['id', 'name', 'fee']);
            });

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'Facilities retrieved successfully (cache failed, using database).',
                'data'    => [
                    'facilities' => $facilities,
                    'from_cache' => false,
                    'cache_error' => $e->getMessage()
                ]
            ], 200);
        }
    }

    public function deleteFacility(Facility $facility)
    {
        try {
            return DB::transaction(function () use ($facility) {
                $facility->delete();

                // Clear cache when facility is deleted
                $this->clearFacilitiesCache();

                return response()->json([
                    'success' => true,
                    'status'  => 200,
                    'message' => 'Facility deleted successfully.',
                ], 200);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to delete facility.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Clear facilities cache
     */
    private function clearFacilitiesCache(): void
    {
        try {
            Cache::forget(self::FACILITIES_CACHE_KEY);
            
            // Alternative using Redis directly
            // Redis::del(self::FACILITIES_CACHE_KEY);
            
        } catch (\Exception $e) {
            // Log cache clear failure but don't break the flow
            \Log::warning('Failed to clear facilities cache: ' . $e->getMessage());
        }
    }

    /**
     * Manually refresh facilities cache
     */
    public function refreshCache(Request $request)
    {
        try {
            // Clear existing cache
            $this->clearFacilitiesCache();
            
            // Reload data into cache
            $facilities = Facility::all()->map(function ($facility) {
                return $facility->only(['id', 'name', 'fee']);
            });
            
            Cache::put(self::FACILITIES_CACHE_KEY, $facilities, self::CACHE_TTL);

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'Facilities cache refreshed successfully.',
                'data'    => [
                    'facilities_count' => $facilities->count(),
                    'cache_key' => self::FACILITIES_CACHE_KEY,
                    'cached_at' => now()->toDateTimeString()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to refresh facilities cache.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache statistics
     */
    public function cacheStats(Request $request)
    {
        try {
            $cacheKey = self::FACILITIES_CACHE_KEY;
            $hasCache = Cache::has($cacheKey);
            
            $stats = [
                'cache_key' => $cacheKey,
                'has_cache' => $hasCache,
                'ttl_seconds' => self::CACHE_TTL,
                'redis_info' => []
            ];

            // Get Redis stats if available
            try {
                $redisInfo = Redis::info();
                $stats['redis_info'] = [
                    'connected_clients' => $redisInfo['connected_clients'] ?? 0,
                    'used_memory_human' => $redisInfo['used_memory_human'] ?? 'unknown',
                    'keyspace_hits' => $redisInfo['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $redisInfo['keyspace_misses'] ?? 0,
                ];
            } catch (\Exception $e) {
                $stats['redis_info']['error'] = $e->getMessage();
            }

            if ($hasCache) {
                // Try to get cache value to verify it's valid
                $cachedData = Cache::get($cacheKey);
                $stats['cached_items_count'] = is_array($cachedData) ? count($cachedData) : 0;
            }

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'Cache statistics retrieved successfully.',
                'data'    => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to get cache statistics.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
