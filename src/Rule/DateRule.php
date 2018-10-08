<?php

namespace phmLabs\MagicUrl\Rule;

class DateRule implements Rule
{
    const DATE_KEYWORD = 'date';

    /**
     * @param $urlString
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException | ResolveException
     */
    public function resolve($urlString)
    {
        if (strpos($urlString, self::DATE_KEYWORD) === 0) {

            $parameters = RuleHelper::extractParameters(self::DATE_KEYWORD, $urlString);

            if (count($parameters) === 0) {
                throw new ResolveException('The date rule needs at least one parameter. None given.');
            }

            if (count($parameters) > 2) {
                throw new ResolveException('The date rule needs two parameters at the most. Given were ' . count($parameters) . '.');
            }

            $format = $parameters[0];

            if (count($parameters) === 1) {
                $dateString = 'now';
            } else {
                $dateString = $parameters[1];
            }

            try {
                $date = new \DateTime($dateString);
            } catch (\Exception $exception) {
                $message = $exception->getMessage();
                $message = str_replace('DateTime::__construct(): ', '', $message);

                throw new ResolveException($message);
            }

            $result = $date->format($format);
            return $result;
        }

        return $urlString;
    }
}
