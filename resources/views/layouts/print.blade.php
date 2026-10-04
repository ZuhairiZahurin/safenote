<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') — {{ config('app.name', 'SafeNote') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            :root {
                --ink: #16212f;
                --ink-soft: #5a6b7f;
                --navy: #102a43;
                --rule: #d7dee7;
                --tint: #f4f7fb;
                --classified: #a4262c;
            }

            body {
                background: #e9edf2;
                color: var(--ink);
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                font-size: 10.5pt;
                line-height: 1.5;
            }

            .sheet {
                background: #fff;
                max-width: 210mm;
                margin: 0 auto 2rem;
                padding: 16mm 15mm;
                box-shadow: 0 10px 30px -18px rgba(16, 42, 67, 0.55);
            }

            /* The classification sits in a repeating table head so that it is
               reprinted at the top of every page of a multi-page document. */
            .print-frame { width: 100%; }
            .print-frame > thead > tr > td { padding-bottom: 10mm; }

            .classification {
                border: 1.5px solid var(--classified);
                color: var(--classified);
                font-weight: 700;
                letter-spacing: 0.18em;
                text-align: center;
                padding: 0.3rem;
                font-size: 8pt;
                text-transform: uppercase;
            }

            /* Letterhead --------------------------------------------------- */

            .letterhead {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                gap: 1rem;
                border-bottom: 2.5px solid var(--navy);
                padding-bottom: 0.6rem;
                margin-bottom: 0.4rem;
            }

            .letterhead .brand {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 15pt;
                font-weight: 700;
                letter-spacing: -0.02em;
                color: var(--navy);
                line-height: 1;
            }

            .letterhead .brand i { color: #2a78d6; }
            .letterhead .unit { font-size: 8.5pt; color: var(--ink-soft); margin-top: 0.25rem; }
            .letterhead .doctype {
                text-align: right;
                font-size: 8pt;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: var(--ink-soft);
                font-weight: 600;
            }

            .doc-title {
                font-size: 13.5pt;
                font-weight: 650;
                margin: 0.9rem 0 0.15rem;
            }

            .doc-meta { font-size: 9pt; color: var(--ink-soft); margin-bottom: 1.2rem; }
            .doc-meta strong { color: var(--ink); font-weight: 600; }

            /* Section headings --------------------------------------------- */

            .sheet h2 {
                font-size: 9pt;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: var(--navy);
                border-bottom: 1px solid var(--rule);
                padding-bottom: 0.3rem;
                margin: 1.5rem 0 0.6rem;
            }

            /* Field list --------------------------------------------------- */

            .fields { display: grid; grid-template-columns: 38mm 1fr; row-gap: 0.45rem; column-gap: 0.75rem; }
            .fields dt { font-size: 9pt; font-weight: 600; color: var(--ink-soft); }
            .fields dd { margin: 0; }

            /* Summary figures ---------------------------------------------- */

            .figures { display: flex; gap: 0.6rem; margin-bottom: 0.4rem; }

            .figure-box {
                flex: 1;
                border: 1px solid var(--rule);
                border-left: 3px solid var(--navy);
                border-radius: 3px;
                padding: 0.5rem 0.7rem;
                background: var(--tint);
            }

            .figure-box .label {
                font-size: 7.5pt;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                color: var(--ink-soft);
                font-weight: 600;
            }

            .figure-box .value { font-size: 17pt; font-weight: 650; line-height: 1.15; font-variant-numeric: tabular-nums; }

            /* Tables ------------------------------------------------------- */

            .sheet table.data {
                width: 100%;
                border-collapse: collapse;
                font-size: 9pt;
                margin-bottom: 0.4rem;
            }

            .sheet table.data th {
                background: var(--tint);
                text-align: left;
                font-size: 7.5pt;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: var(--ink-soft);
                border-top: 1px solid var(--rule);
                border-bottom: 1px solid var(--rule);
                padding: 0.35rem 0.5rem;
            }

            .sheet table.data td {
                border-bottom: 1px solid #eef2f7;
                padding: 0.32rem 0.5rem;
                vertical-align: middle;
            }

            .sheet table.data tbody tr:nth-child(even) td { background: #fbfcfe; }
            .sheet table.data .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
            .sheet table.data tfoot td { font-weight: 700; border-top: 1.5px solid var(--navy); background: #fff; }

            /* A light proportion bar that still reads when printed in mono. */
            .bar-cell { width: 34mm; }
            .bar { height: 7px; background: #e6ebf2; border-radius: 2px; overflow: hidden; }
            .bar > span { display: block; height: 100%; background: #6b8cb5; }

            .notes {
                white-space: pre-wrap;
                border: 1px solid var(--rule);
                border-radius: 3px;
                padding: 0.8rem 0.9rem;
                min-height: 45mm;
                background: #fdfdfe;
            }

            .note-empty { text-align: center; color: var(--ink-soft); border: 1px dashed var(--rule); border-radius: 3px; padding: 1.5rem; font-size: 9pt; }

            /* Signature and footer ----------------------------------------- */

            .closing { break-inside: avoid; }

            .signatures { display: flex; gap: 2rem; margin-top: 14mm; }
            .signature { flex: 1; max-width: 62mm; }
            .signature .line { border-bottom: 1px solid var(--ink); height: 12mm; }
            .signature .caption { font-size: 8pt; color: var(--ink-soft); padding-top: 0.3rem; }

            .print-footer {
                border-top: 1px solid var(--rule);
                margin-top: 10mm;
                padding-top: 0.45rem;
                font-size: 7.5pt;
                color: var(--ink-soft);
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                flex-wrap: wrap;
            }

            /* Screen-only toolbar ------------------------------------------ */

            .toolbar {
                position: sticky;
                top: 0;
                z-index: 5;
                background: #fff;
                border-bottom: 1px solid var(--rule);
                box-shadow: 0 1px 8px rgba(16, 42, 67, 0.06);
                padding: 0.6rem 0;
                margin-bottom: 1.6rem;
            }

            .toolbar .btn { border-radius: 7px; }
            .toolbar .form-control, .toolbar .form-select { border-radius: 7px; }

            @page { size: A4; margin: 14mm; }

            @media print {
                body { background: #fff; font-size: 10pt; }
                .no-print { display: none !important; }
                .sheet { box-shadow: none; margin: 0; padding: 0; max-width: none; }
                .classification,
                .figure-box,
                .sheet table.data th,
                .sheet table.data tbody tr:nth-child(even) td,
                .bar, .bar > span {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                tr { break-inside: avoid; }
                h2 { break-after: avoid; }
            }
        </style>
    </head>
    <body>
        <div class="no-print toolbar">
            <div class="container d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <a href="@yield('back-url')" class="btn btn-link btn-sm ps-0 text-decoration-none">&larr; {{ __('Back') }}</a>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    @yield('toolbar')
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer"></i> {{ __('Print / Save as PDF') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="sheet">
            <table class="print-frame">
                <thead>
                    <tr><td><div class="classification">SULIT / CONFIDENTIAL</div></td></tr>
                </thead>
                <tbody>
                    <tr><td>
                        <div class="letterhead">
                            <div>
                                <div class="brand"><i class="bi bi-shield-lock-fill"></i> SafeNote</div>
                                <div class="unit">{{ __('Unit Bimbingan dan Kaunseling · SMK Pandan Indah') }}</div>
                            </div>
                            <div class="doctype">@yield('doctype', __('Counselling Document'))</div>
                        </div>

                        @yield('content')

                        <div class="closing">
                            <div class="signatures">
                                <div class="signature">
                                    <div class="line"></div>
                                    <div class="caption">{{ __('Counsellor signature') }}</div>
                                </div>
                                <div class="signature">
                                    <div class="line"></div>
                                    <div class="caption">{{ __('Date') }}</div>
                                </div>
                            </div>

                            <div class="print-footer">
                                <span>{{ __('Printed by') }} <strong>{{ auth()->user()->name }}</strong> {{ __('on') }} {{ now()->format('d M Y, H:i') }}</span>
                                <span>{{ __('Handle under Akta Kaunselor 1998 and PDPA 2010. Do not leave unattended.') }}</span>
                            </div>
                        </div>
                    </td></tr>
                </tbody>
            </table>
        </div>
    </body>
</html>
