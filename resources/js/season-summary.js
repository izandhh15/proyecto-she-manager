import {
    canvasToBlob,
    createCanvasContext,
    downloadCanvas,
    drawBrandFooter,
    drawDivider,
    drawSectionLabel,
    drawStatsRow,
    drawTeamHeader,
    fillBackground,
    trimCanvas,
} from './modules/canvas-image';

const SUMMARY_WIDTH = 800;
const SUMMARY_HEIGHT = 1400;

function buildFilename(teamName) {
    return `${teamName.replace(/[^a-zA-Z0-9]/g, '_')}_season.png`;
}

export default function seasonSummary(config) {
    return {
        teamName: config.teamName,
        teamCrestUrl: config.teamCrestUrl,
        subtitle: config.subtitle,
        subtitleColor: config.subtitleColor,
        record: config.record,
        highlights: config.highlights,
        homeRecord: config.homeRecord,
        awayRecord: config.awayRecord,
        otherCompetitions: config.otherCompetitions,
        labels: config.labels,
        previewUrl: null,
        copied: false,
        isGeneratingPreview: false,

        init() {
            this.refreshPreview();
        },

        async buildSummaryCanvas() {
            const { canvas, ctx, width, padding, contentWidth } = createCanvasContext(SUMMARY_WIDTH, SUMMARY_HEIGHT);
            fillBackground(ctx, width, SUMMARY_HEIGHT);

            if (document.fonts?.ready) {
                await document.fonts.ready;
            }

            let y = padding;

            y = await drawTeamHeader(ctx, {
                crestUrl: this.teamCrestUrl,
                name: this.teamName,
                subtitle: this.subtitle,
                subtitleColor: this.subtitleColor,
                padding,
                width,
                y,
            });

            const goalDifference = this.record.gf - this.record.ga;
            y = drawStatsRow(ctx, [
                { label: this.labels.played, value: this.record.played, color: '#ffffff' },
                { label: this.labels.won, value: this.record.won, color: '#22c55e' },
                { label: this.labels.drawn, value: this.record.drawn, color: '#94a3b8' },
                { label: this.labels.lost, value: this.record.lost, color: '#ef4444' },
                { label: this.labels.gf, value: this.record.gf, color: '#ffffff' },
                { label: this.labels.ga, value: this.record.ga, color: '#ffffff' },
                {
                    label: this.labels.gd,
                    value: `${goalDifference >= 0 ? '+' : ''}${goalDifference}`,
                    color: goalDifference >= 0 ? '#22c55e' : '#ef4444',
                },
                { label: this.labels.pts, value: this.record.pts, color: '#f59e0b' },
            ], { padding, contentWidth, y });

            if (this.highlights.length > 0) {
                drawSectionLabel(ctx, this.labels.teamHighlights, padding, y);
                y += 18;

                for (const highlight of this.highlights) {
                    ctx.fillStyle = '#e2e8f0';
                    ctx.font = '400 14px Inter, sans-serif';
                    ctx.fillText(highlight.playerName, padding, y);

                    const valueText = String(highlight.value);
                    const labelText = highlight.label.toUpperCase();
                    const rightEdge = width - padding;
                    const valueWidth = ctx.measureText(valueText).width;

                    ctx.fillStyle = '#64748b';
                    ctx.font = '600 10px Inter, sans-serif';
                    const labelWidth = ctx.measureText(labelText).width;
                    ctx.fillText(labelText, rightEdge - labelWidth, y);

                    ctx.fillStyle = '#cbd5e1';
                    ctx.font = '600 13px Inter, sans-serif';
                    ctx.fillText(valueText, rightEdge - labelWidth - valueWidth - 6, y);

                    y += 22;
                }

                y += 14;
                drawDivider(ctx, padding, width - padding, y);
                y += 14;
            }

            y += 8;
            drawSectionLabel(ctx, this.labels.homeRecord, padding, y);
            drawSectionLabel(ctx, this.labels.awayRecord, padding + contentWidth / 2, y);
            y += 18;

            const drawRecord = (record, x) => {
                const items = [
                    { value: record.w, color: '#22c55e', label: this.labels.won },
                    { value: record.d, color: '#94a3b8', label: this.labels.drawn },
                    { value: record.l, color: '#ef4444', label: this.labels.lost },
                ];

                let offsetX = x;
                for (const item of items) {
                    const valueText = String(item.value);

                    ctx.fillStyle = item.color;
                    ctx.font = 'bold 20px Inter, sans-serif';
                    ctx.fillText(valueText, offsetX, y + 2);
                    offsetX += ctx.measureText(valueText).width + 3;

                    ctx.fillStyle = '#64748b';
                    ctx.font = '600 10px Inter, sans-serif';
                    ctx.fillText(item.label, offsetX, y + 2);
                    offsetX += ctx.measureText(item.label).width + 12;
                }
            };

            drawRecord(this.homeRecord, padding);
            drawRecord(this.awayRecord, padding + contentWidth / 2);
            y += 20;

            if (this.otherCompetitions.length > 0) {
                drawDivider(ctx, padding, width - padding, y);
                y += 20;

                drawSectionLabel(ctx, this.labels.otherCompetitions, padding, y);
                y += 18;

                for (const competition of this.otherCompetitions) {
                    ctx.fillStyle = '#e2e8f0';
                    ctx.font = '600 14px Inter, sans-serif';
                    ctx.fillText(competition.name, padding, y);

                    const resultText = competition.result;
                    ctx.fillStyle = competition.isChampion ? '#f59e0b' : '#94a3b8';
                    ctx.font = '400 13px Inter, sans-serif';
                    ctx.fillText(resultText, width - padding - ctx.measureText(resultText).width, y);

                    y += 22;
                }
            }

            y = drawBrandFooter(ctx, width, y);

            return {
                canvas: trimCanvas(canvas, y),
                filename: buildFilename(this.teamName),
            };
        },

        async refreshPreview() {
            if (this.isGeneratingPreview) {
                return;
            }

            this.isGeneratingPreview = true;

            try {
                const { canvas } = await this.buildSummaryCanvas();
                this.previewUrl = canvas.toDataURL('image/png');
            } catch (error) {
                console.error('Failed to generate season summary preview.', error);
            } finally {
                this.isGeneratingPreview = false;
            }
        },

        async downloadSeasonImage() {
            try {
                const { canvas, filename } = await this.buildSummaryCanvas();
                downloadCanvas(canvas, filename);
            } catch (error) {
                console.error('Failed to download season summary.', error);
            }
        },

        async shareSeasonImage({ text, url, fallbackUrl }) {
            try {
                const { canvas, filename } = await this.buildSummaryCanvas();

                if (navigator.share) {
                    const blob = await canvasToBlob(canvas);
                    const file = new File([blob], filename, { type: 'image/png' });
                    const sharePayload = {
                        title: `${this.teamName} · ${this.subtitle}`,
                        text,
                        url,
                    };

                    if (navigator.canShare?.({ files: [file] })) {
                        await navigator.share({
                            ...sharePayload,
                            files: [file],
                        });
                        return true;
                    }

                    await navigator.share(sharePayload);
                    return true;
                }
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    console.error('Failed to share season summary.', error);
                }
            }

            if (fallbackUrl) {
                window.open(fallbackUrl, '_blank', 'noopener');
                return true;
            }

            return false;
        },

        async copyShareText(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.copied = true;

                clearTimeout(this._copiedTimer);
                this._copiedTimer = setTimeout(() => {
                    this.copied = false;
                }, 2200);
            } catch (error) {
                console.error('Failed to copy share text.', error);
            }
        },
    };
}
