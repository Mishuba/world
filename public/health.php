<?
app.get("/health", (req, res) => {
  res.json({ ok: true });
});

?>