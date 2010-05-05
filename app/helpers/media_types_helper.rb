module MediaTypesHelper
  def parent_options_tree(tree, current, tab = 1, include_blank = true)
    output = String.new
    output << "<option value=\"\">Disc</option>" if include_blank
    tree.each do |element, branch|
      next if current.id == element.id
      output << "<option value=\"#{element.id}\""
      output << " selected=\"selected\"" if current.parent_id == element.id
      output << ">"
      (tab*4).times { output << "&nbsp;" }
      output << h(element.title)
      output << "</option>"
      output << parent_options_tree(branch, current, tab+1, false) unless branch.blank?
    end
    output
  end
end
