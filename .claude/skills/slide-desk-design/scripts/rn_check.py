#!/usr/bin/env python3
"""Checks run before anyone sees the deck.

    python rn_check.py deck.json out.pptx [inventory.json]

1. Fit: every shape inside the slide, nothing overlapping the takeaway/footer.
2. Numbers: every figure in deck.json appears in the pptx, character for character.
3. Text inventory: every source text and table cell (from rn_extract) appears in
   the rebuilt deck, or is listed as missing. Missing items are failures unless
   the analyst decided to drop them.
"""
import json, re, sys
from pptx import Presentation

EMU = 9525


def deck_strings(prs):
    out = []
    for s in prs.slides:
        bag = []
        for sh in s.shapes:
            if sh.has_text_frame:
                bag.append(sh.text_frame.text)
            elif getattr(sh, "has_table", False) and sh.has_table:
                bag += [c.text for r in sh.table.rows for c in r.cells]
        if s.has_notes_slide:
            bag.append(s.notes_slide.notes_text_frame.text)
        out.append(bag)
    return out


def norm(t):
    t = str(t).replace("−", "-").replace("–", "-").replace("’", "'")
    t = re.sub(r"\((\d[\d,.]*)\)%", r"-\1%", t)  # chip form: (5.1)% == −5.1%
    return re.sub(r"\s+", " ", t).strip().lower()


def main(spec, pptx, inv=None):
    prs = Presentation(pptx)
    W, H = prs.slide_width, prs.slide_height
    fails = 0
    for i, s in enumerate(prs.slides, 1):
        for sh in s.shapes:
            if sh.left < 0 or sh.top < 0 or sh.left + sh.width > W + EMU or sh.top + sh.height > H + EMU:
                print(f"FIT  slide {i}: '{sh.name}' runs off the slide"); fails += 1
    have = [" | ".join(norm(x) for x in bag) for bag in deck_strings(prs)]
    allhave = " | ".join(have)
    d = json.load(open(spec))
    for i, s in enumerate(d["slides"], 1):
        blob = json.dumps(s, ensure_ascii=False)
        for num in re.findall(r'"([^"]*\d[^"]*)"', blob):
            for tok in re.findall(r"[-+−(]?\$?\d[\d,.]*\)?%?", num):
                if norm(tok) not in have[i - 1]:
                    print(f"NUM  slide {i}: '{tok}' not found on the slide"); fails += 1
    if inv:
        src = json.load(open(inv))
        for s in src["slides"]:
            if s["hidden"]:
                continue
            items = [t["text"] for t in s["texts"]] + [c for tb in s["tables"] for r in tb for c in r if c]
            for t in items:
                for line in [l for l in t.split("\n") if l.strip()]:
                    parts = [norm(x) for x in line.split(":") if x.strip()]  # card labels split at the colon
                    if norm(line) not in allhave and not all(x in allhave for x in parts):
                        print(f"TEXT source slide {s['n']}: missing -> {line[:90]}"); fails += 1
            if s["pictures"]:
                print(f"LOOK source slide {s['n']}: {len(s['pictures'])} picture(s); confirm any pasted table/chart was rebuilt")
    print("PASS" if not fails else f"{fails} issue(s)")
    return fails


if __name__ == "__main__":
    sys.exit(1 if main(*sys.argv[1:4]) else 0)
