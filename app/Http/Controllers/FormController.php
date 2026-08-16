<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriberRequest;
use App\Http\Requests\WaitlistSubmissionRequest;
use App\Models\Subscriber;
use App\Models\WaitlistSubmission;
use Illuminate\Http\JsonResponse;

/**
 * The two public forms. Both live inside page content stored in the database,
 * so they post via fetch() with the X-CSRF-TOKEN header rather than @csrf.
 */
class FormController extends Controller
{
    public function subscribe(SubscriberRequest $request): JsonResponse
    {
        // Subscribing twice is not an error on a joke site: the second attempt
        // just refreshes the metadata and reports success like the first.
        Subscriber::updateOrCreate(
            ['email' => $request->string('email')->lower()->toString()],
            $this->metadata($request->ip(), $request->userAgent()),
        );

        return response()->json(['ok' => true]);
    }

    public function waitlist(WaitlistSubmissionRequest $request): JsonResponse
    {
        $email = $request->string('email')->lower()->toString();

        $submission = WaitlistSubmission::create([
            ...$request->validated(),
            'email' => $email,
            'position' => WaitlistSubmission::positionFor($email),
            ...$this->metadata($request->ip(), $request->userAgent()),
        ]);

        return response()->json([
            'ok' => true,
            'position' => $submission->position,
        ]);
    }

    /** @return array{locale: string, ip: ?string, user_agent: ?string} */
    protected function metadata(?string $ip, ?string $userAgent): array
    {
        return [
            'locale' => app()->getLocale(),
            'ip' => $ip,
            'user_agent' => $userAgent,
        ];
    }
}
