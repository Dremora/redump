module DiscsHelper
  def status(disc)
    Disc.statuses[disc.status].to_s.gsub('_', ' ').capitalize
  end
  
  def status_image(disc)
    raw '<img src="/images/status/' + Disc.statuses[disc.status].to_s + '.png" />'
  end
  
  def status_image_text(disc)
    status_image(disc) + ' ' + status(disc)
  end
end
