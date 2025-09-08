<?php

return [
    'Generate ALT text'                                         => 'Generate ALT text',
    'Queued ALT generation.'                                    => 'Queued ALT generation.',
    'Queued ALT generation for {n} asset(s).'                   => 'Queued ALT generation for {n} asset(s).',

    // Settings UI
    'Img Alt — Settings'                                        => 'Img Alt — Settings',
    'Auto-generate on upload'                                   => 'Auto-generate on upload',
    'When enabled, the plugin will queue alt-text generation whenever an image is uploaded.' => 'When enabled, the plugin will queue alt-text generation whenever an image is uploaded.',
    'OpenAI API Key'                                            => 'OpenAI API Key',
    'Supports plain value or an environment variable like {example}.' => 'Supports plain value or an environment variable like {example}.',
    'Max tokens'                                                => 'Max tokens',
    'Upper bound for a single generation request.'              => 'Upper bound for a single generation request.',
    'Language'                                                  => 'Language',
    'BCP-47 code to bias generated text (e.g. en, en-AU).'      => 'BCP-47 code to bias generated text (e.g. en, en-AU).',
    'Send image as upload'                                      => 'Send image as upload',
    'If enabled, the plugin will upload image bytes to OpenAI. If disabled, it will pass a public URL so OpenAI can fetch the image.' => 'If enabled, the plugin will upload image bytes to OpenAI. If disabled, it will pass a public URL so OpenAI can fetch the image.',
    'Max dimension (px)'                                        => 'Max dimension (px)',
    'Longest side of the transformed image.'                    => 'Longest side of the transformed image.',
    'Resize mode'                                               => 'Resize mode',
    'Fit (preserve aspect)'                                     => 'Fit (preserve aspect)',
    'Crop'                                                      => 'Crop',
    'Stretch'                                                   => 'Stretch',
    'Output format'                                             => 'Output format',
    'Choose an output format to reduce size. Leave blank to keep source format.' => 'Choose an output format to reduce size. Leave blank to keep source format.',
    'JPEG (jpg)'                                                => 'JPEG (jpg)',
    'PNG (png)'                                                 => 'PNG (png)',
    'WebP (webp)'                                               => 'WebP (webp)',
    'Source format (unchanged)'                                 => 'Source format (unchanged)',
    'Quality'                                                   => 'Quality',
    'Applies to lossy formats (jpg/webp).'                      => 'Applies to lossy formats (jpg/webp).',

    // Jobs / logging
    'Asset ID {id} not found.'                                  => 'Asset ID {id} not found.',
    'Failed to generate alt text for asset ID {id}'             => 'Failed to generate alt text for asset ID {id}',
    'Generating alt text for asset ID {id}'                     => 'Generating alt text for asset ID {id}',

    // Prompt / errors
    'Write one short alt text sentence for this image, suitable for accessibility and SEO. Describe the image clearly and concisely. Do not use quotes, colons or semi-colons. Limit to 10–20 words.' => 'Write one short alt text sentence for this image, suitable for accessibility and SEO. Describe the image clearly and concisely. Do not use quotes, colons or semi-colons. Limit to 10–20 words.',
    'No public URL for this asset. Enable “Send image as upload”.' => 'No public URL for this asset. Enable “Send image as upload”.',
    'Failed to read bytes for asset #{id}'                       => 'Failed to read bytes for asset #{id}',
];
