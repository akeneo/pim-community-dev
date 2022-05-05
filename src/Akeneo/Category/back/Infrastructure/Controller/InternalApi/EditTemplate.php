<?php

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditTemplate
{
    public function __construct()
    {
    }

    public function __invoke(Request $request): Response
    {
        /*$violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->serializer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }*/

        //($this->editReferenceEntityHandler)($command);
        $template = json_decode($request->getContent(), true);
        /*
                template['attributes'] = [
                    [code=> 'a_text_value', 'type' => 'text']
                ]
                    TextAttribute::createText(

                    )

        */
        return new JsonResponse($template);
    }
}
